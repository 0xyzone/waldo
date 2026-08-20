<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\EmployeeSuspension;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('suspensions:check-status')]
#[Description('Check employee suspensions, uplift expired suspensions back to Active, and apply starting suspensions')]
class CheckSuspensionStatusCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today();
        $this->info("Checking employee suspension statuses for {$today->toDateString()}...");

        // 1. Find all active suspensions whose end_date has passed (< today)
        $expiredSuspensions = EmployeeSuspension::where('status', 'active')
            ->whereDate('end_date', '<', $today)
            ->get();

        $upliftedCount = 0;
        foreach ($expiredSuspensions as $suspension) {
            $suspension->update(['status' => 'completed']);
            $upliftedCount++;

            // Check if this employee has any other active suspension
            $hasOtherActive = EmployeeSuspension::where('employee_id', $suspension->employee_id)
                ->where('status', 'active')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->exists();

            if (! $hasOtherActive) {
                $employee = Employee::where('employee_code', $suspension->employee_id)->first();
                if ($employee && $employee->employee_status === 'Suspended') {
                    $employee->update(['employee_status' => 'Active']);
                    $this->line("Restored employee {$suspension->employee_id} status to Active.");
                }
            }
        }

        // 2. Also check if there are active suspensions currently within range today that need employee status set
        $currentActiveSuspensions = EmployeeSuspension::where('status', 'active')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->get();

        $appliedCount = 0;
        foreach ($currentActiveSuspensions as $suspension) {
            $employee = Employee::where('employee_code', $suspension->employee_id)->first();
            if ($employee && ($employee->employee_status !== 'Suspended' || $employee->tips_status !== 'Hold')) {
                $employee->update([
                    'employee_status' => 'Suspended',
                    'tips_status' => 'Hold',
                ]);
                $appliedCount++;
                $this->line("Applied suspension status to employee {$suspension->employee_id}.");
            }
        }

        $this->info("Suspension status check complete. {$upliftedCount} uplifted, {$appliedCount} applied/updated.");

        return Command::SUCCESS;
    }
}
