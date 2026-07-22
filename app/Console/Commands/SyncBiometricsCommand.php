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
            $res = $syncService->sync();
            $pulled = $res['pulled'] ?? 0;
            $pushed = $res['pushed'] ?? 0;
            $this->info("Successfully synced! Pulled {$pulled} records from Google Sheet and pushed {$pushed} records to Google Sheet.");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Sync failed: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
