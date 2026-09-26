<?php

namespace App\Console\Commands;

use App\Models\ScheduleRun;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('schedule-runs:prune')]
#[Description('Delete schedule run records older than 2 days')]
class PruneScheduleRunsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $threshold = now()->subDays(2);

        $deleted = ScheduleRun::where(function ($query) use ($threshold) {
            $query->where('started_at', '<', $threshold)
                ->orWhere(function ($q) use ($threshold) {
                    $q->whereNull('started_at')
                        ->where('created_at', '<', $threshold);
                });
        })->delete();

        $this->info("Pruned {$deleted} schedule run record(s) older than 2 days.");

        return Command::SUCCESS;
    }
}
