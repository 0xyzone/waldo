<?php

namespace App\Console\Commands;

use App\Jobs\SyncPromotionToSheetJob;
use App\Jobs\SyncTransferToSheetJob;
use App\Models\Employee;
use App\Models\EmployeePromotion;
use App\Models\EmployeeTransfer;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('transitions:apply-effective')]
#[Description('Apply promotions and transfers that are effective on or before today')]
class ApplyEffectiveTransitionsCommand extends Command
{
    public function handle(): int
    {
        $today = Carbon::today()->toDateString();
        $this->info("Checking effective promotions and transfers for {$today}...");

        $promotionsApplied = 0;
        $transfersApplied = 0;

        // Apply effective promotions
        $effectivePromotions = EmployeePromotion::whereDate('promotion_date', '<=', $today)
            ->where('hrms_synced', false)
            ->orderBy('promotion_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($effectivePromotions as $promotion) {
            $employee = Employee::where('employee_code', $promotion->employee_id)->first();
            if (! $employee) {
                continue;
            }

            $updates = [];
            if ($promotion->to_department_id !== null) {
                $updates['department_id'] = $promotion->to_department_id;
            }
            if ($promotion->to_designation_id !== null) {
                $updates['designation_id'] = $promotion->to_designation_id;
            }

            if (! empty($updates)) {
                $employee->update($updates);
            }

            $promotion->update([
                'hrms_synced' => true,
                'hrms_synced_at' => now(),
            ]);

            SyncPromotionToSheetJob::dispatch($promotion->id, null);
            $promotionsApplied++;
        }

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
