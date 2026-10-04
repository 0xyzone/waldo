<?php

namespace App\Console\Commands;

use App\Jobs\SyncTransferToSheetJob;
use App\Models\Employee;
use App\Models\EmployeeTransfer;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('transitions:apply-effective')]
#[Description('Apply transfers that are effective on or before today')]
class ApplyEffectiveTransitionsCommand extends Command
{
    public function handle(): int
    {
        $today = Carbon::today()->toDateString();
        $this->info("Checking effective transfers for {$today}...");

        $transfersApplied = 0;

        // Apply effective transfers
        $effectiveTransfers = EmployeeTransfer::whereDate('transfer_date', '<=', $today)
            ->where('hrms_synced', false)
            ->orderBy('transfer_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($effectiveTransfers as $transfer) {
            $employee = Employee::where('employee_code', $transfer->employee_id)->first();
            if (! $employee) {
                continue;
            }

            $updates = [];
            if ($transfer->to_department_id !== null) {
                $updates['department_id'] = $transfer->to_department_id;
            }
            if ($transfer->to_designation_id !== null) {
                $updates['designation_id'] = $transfer->to_designation_id;
            }

            if (! empty($updates)) {
                $employee->update($updates);
            }

            $transfer->update([
                'hrms_synced' => true,
                'hrms_synced_at' => now(),
            ]);

            SyncTransferToSheetJob::dispatch($transfer->id, null);
            $transfersApplied++;
        }

        $this->info("Done. Applied {$promotionsApplied} promotions, {$transfersApplied} transfers.");

        return Command::SUCCESS;
    }
}
