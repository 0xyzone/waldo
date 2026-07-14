<?php

namespace App\Console\Commands;

use App\Services\BiometricSheetsService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('biometrics:sync')]
#[Description('Sync biometric allotment records from Google Sheets into the application database')]
class SyncBiometricsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(BiometricSheetsService $syncService): int
    {
        $this->info('Starting biometric allotment synchronization from Google Sheets...');

        try {
            $count = $syncService->sync();
            $this->info("Successfully synced {$count} records.");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Sync failed: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
