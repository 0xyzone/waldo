<?php

namespace App\Observers;

use App\Models\BiometricAllotment;
use App\Services\BiometricSheetsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BiometricAllotmentObserver
{
    protected BiometricSheetsService $sheetsService;

    public function __construct(BiometricSheetsService $sheetsService)
    {
        $this->sheetsService = $sheetsService;
    }

    /**
     * Handle the BiometricAllotment "creating" event.
     */
    public function creating(BiometricAllotment $allotment): void
    {
        if (empty($allotment->code)) {
            try {
                $driver = DB::connection()->getDriverName();
                if ($driver === 'sqlite') {
                    $maxCodeNum = BiometricAllotment::selectRaw('MAX(CAST(SUBSTR(code, 4) AS INTEGER)) as max_num')->value('max_num');
                } else {
                    $maxCodeNum = BiometricAllotment::selectRaw('MAX(CAST(SUBSTR(code, 4) AS UNSIGNED)) as max_num')->value('max_num');
                }
            } catch (\Exception $e) {
                $maxCodeNum = null;
            }

            if ($maxCodeNum === null) {
                // PHP fallback to find max code
                $maxCodeNum = 0;
                $allCodes = BiometricAllotment::pluck('code')->toArray();
                foreach ($allCodes as $code) {
                    $num = (int) substr($code, 3);
                    if ($num > $maxCodeNum) {
                        $maxCodeNum = $num;
                    }
                }
            }

            $nextNum = max(649, (int) $maxCodeNum) + 1;
            $allotment->code = 'CWD'.$nextNum;
            Log::info('Auto-generated code for new biometric allotment', ['code' => $allotment->code]);
        }
    }

    /**
     * Handle the BiometricAllotment "saved" event.
     */
    public function saved(BiometricAllotment $allotment): void
    {
        Log::info('BiometricAllotmentObserver saved triggered', [
            'code' => $allotment->code,
            'wasRecentlyCreated' => $allotment->wasRecentlyCreated,
            'changes' => $allotment->getChanges(),
        ]);

        $changedFields = $allotment->wasRecentlyCreated
            ? null
            : array_keys($allotment->getChanges());

        $this->sheetsService->syncAllotment($allotment, $changedFields);

        // Optional backward sync from Google Sheet can be triggered here if desired,
        // but for biometrics, running it via a manual action or scheduled task is usually preferred
        // to avoid slow request cycles. We'll stick to a manual button/command.
    }

    /**
     * Handle the BiometricAllotment "deleted" event.
     */
    public function deleted(BiometricAllotment $allotment): void
    {
        $this->sheetsService->deleteAllotment($allotment->code);
    }
}
