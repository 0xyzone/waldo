<?php

namespace App\Listeners;

use App\Models\ScheduleRun;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskSkipped;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Events\Dispatcher;

class RecordScheduleRunListener
{
    /**
     * Cache active run records by task mutex/command description.
     *
     * @var array<string, ScheduleRun>
     */
    protected static array $activeRuns = [];

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            ScheduledTaskStarting::class => 'handleStarting',
            ScheduledTaskFinished::class => 'handleFinished',
            ScheduledTaskFailed::class => 'handleFailed',
            ScheduledTaskSkipped::class => 'handleSkipped',
        ];
    }

    public function handleStarting(ScheduledTaskStarting $event): void
    {
        $command = $this->resolveTaskCommand($event->task);

        $run = ScheduleRun::create([
            'command' => $command,
            'status' => 'running',
            'started_at' => now(),
        ]);

        static::$activeRuns[$this->taskKey($event->task)] = $run;
    }

    public function handleFinished(ScheduledTaskFinished $event): void
    {
        $key = $this->taskKey($event->task);
        $run = static::$activeRuns[$key] ?? null;

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
            unset(static::$activeRuns[$key]);
        } else {
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
            unset(static::$activeRuns[$key]);
        } else {
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
