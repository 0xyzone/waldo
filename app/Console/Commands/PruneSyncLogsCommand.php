<?php

namespace App\Console\Commands;

use App\Models\SyncLog;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('sync-logs:prune')]
#[Description('Delete employee sync logs older than 2 days')]
class PruneSyncLogsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $deleted = SyncLog::where('created_at', '<', now()->subDays(2))->delete();

        $this->info("Pruned {$deleted} sync log(s) older than 2 days.");

        return Command::SUCCESS;
    }
}
