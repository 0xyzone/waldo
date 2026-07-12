<?php

namespace App\Console\Commands;

use App\Services\EmployeeSyncService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('employees:sync')]
#[Description('Sync employee records from Google Sheets into the application database')]
class SyncEmployeesCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(EmployeeSyncService $syncService): int
    {
        $this->info('Starting employee synchronization from Google Sheets...');

        try {
            $count = $syncService->sync();
            $this->info("Successfully synced {$count} employees.");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Sync failed: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
