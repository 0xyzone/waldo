<?php

namespace App\Listeners;

use App\Models\ScheduleRun;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskSkipped;
use Illuminate\Console\Events\ScheduledTaskStarting;

class RecordScheduleRunListener
{
    /**
     * Cache active run records by task mutex/command description.
     *
     * @var array<string, ScheduleRun>
     */
    protected static array $activeRuns = [];

    public function handleStarting(ScheduledTaskStarting $event): void
    {
        $key = $this->taskKey($event->task);
        if (isset(static::$activeRuns[$key])) {
            return;
        }

        $command = $this->resolveTaskCommand($event->task);

        // Deduplication safeguard: if a running record for this command started within the last 5 seconds exists, reuse it
        $existing = ScheduleRun::where('command', $command)
            ->where('status', 'running')
            ->where('started_at', '>=', now()->subSeconds(5))
            ->first();

        if ($existing) {
            static::$activeRuns[$key] = $existing;

            return;
        }

        $run = ScheduleRun::create([
            'command' => $command,
            'status' => 'running',
            'started_at' => now(),
        ]);

        static::$activeRuns[$key] = $run;
    }

    public function handleFinished(ScheduledTaskFinished $event): void
    {
        $key = $this->taskKey($event->task);
        $run = static::$activeRuns[$key] ?? null;
        unset(static::$activeRuns[$key]);

        $command = $this->resolveTaskCommand($event->task);
        $now = now();
        $duration = (float) $event->runtime;

        if (! $run) {
            $run = ScheduleRun::where('command', $command)
                ->where('status', 'running')
                ->latest('started_at')
                ->first();
        }

        if ($run) {
            $run->update([
                'status' => 'success',
                'finished_at' => $now,
                'duration_seconds' => $duration,
                'exit_code' => 0,
            ]);
        } else {
            // Deduplication safeguard: don't create fallback if a success run was already recorded within the last 5 seconds
            $recent = ScheduleRun::where('command', $command)
                ->where('status', 'success')
                ->where('finished_at', '>=', $now->copy()->subSeconds(5))
                ->first();

            if ($recent) {
                return;
            }

            ScheduleRun::create([
                'command' => $command,
                'status' => 'success',
                'started_at' => $now->copy()->subSeconds((int) round($duration)),
                'finished_at' => $now,
                'duration_seconds' => $duration,
                'exit_code' => 0,
            ]);
        }
    }

    public function handleFailed(ScheduledTaskFailed $event): void
    {
        $key = $this->taskKey($event->task);
        $run = static::$activeRuns[$key] ?? null;
        unset(static::$activeRuns[$key]);

        $command = $this->resolveTaskCommand($event->task);
        $now = now();
        $exception = $event->exception;
        $output = $exception ? ($exception->getMessage()."\n".$exception->getTraceAsString()) : null;

        if (! $run) {
            $run = ScheduleRun::where('command', $command)
                ->where('status', 'running')
                ->latest('started_at')
                ->first();
        }

        if ($run) {
            $run->update([
                'status' => 'failed',
                'finished_at' => $now,
                'duration_seconds' => $run->started_at ? (float) $now->diffInMicroseconds($run->started_at) / 1000000 : null,
                'exit_code' => 1,
                'output' => $output,
            ]);
        } else {
            // Deduplication safeguard: don't create fallback if a failed run was already recorded within the last 5 seconds
            $recent = ScheduleRun::where('command', $command)
                ->where('status', 'failed')
                ->where('finished_at', '>=', $now->copy()->subSeconds(5))
                ->first();

            if ($recent) {
                return;
            }

            ScheduleRun::create([
                'command' => $command,
                'status' => 'failed',
                'started_at' => $now,
                'finished_at' => $now,
                'exit_code' => 1,
                'output' => $output,
            ]);
        }
    }

    public function handleSkipped(ScheduledTaskSkipped $event): void
    {
        $key = $this->taskKey($event->task);
        $command = $this->resolveTaskCommand($event->task);

        $run = static::$activeRuns[$key]
            ?? ScheduleRun::where('command', $command)
                ->where('status', 'running')
                ->latest('started_at')
                ->first();

        if ($run) {
            $run->delete();
        }

        unset(static::$activeRuns[$key]);
    }

    protected function resolveTaskCommand($task): string
    {
        $description = $task->description ?? null;
        if (! empty($description)) {
            return $description;
        }

        $command = $task->command ?? null;
        if (! empty($command)) {
            // Strip php binary path if present
            $cleaned = preg_replace('/^.*?artisan(?:\.php)?\s*/i', 'php artisan ', $command);

            return trim($cleaned);
        }

        return 'Scheduled Closure / Callback';
    }

    protected function taskKey($task): string
    {
        if (method_exists($task, 'mutexName')) {
            return $task->mutexName();
        }

        return spl_object_hash($task);
    }
}
