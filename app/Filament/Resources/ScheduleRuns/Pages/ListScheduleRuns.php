<?php

namespace App\Filament\Resources\ScheduleRuns\Pages;

use App\Filament\Resources\ScheduleRuns\ScheduleRunResource;
use App\Models\ScheduleRun;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;

class ListScheduleRuns extends ListRecords
{
    protected static string $resource = ScheduleRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('runSingleTask')
                ->label('Run Task On-Demand')
                ->icon('heroicon-m-play')
                ->color('success')
                ->form([
                    Select::make('task')
                        ->label('Select Scheduled Task')
                        ->options($this->getScheduledTasks())
                        ->required()
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $command = $data['task'];
                    $startedAt = now();

                    $run = ScheduleRun::create([
                        'command' => 'php artisan '.$command,
                        'status' => 'running',
                        'started_at' => $startedAt,
                    ]);

                    try {
                        $exitCode = Artisan::call($command);
                        $output = trim(Artisan::output());
                        $finishedAt = now();
                        $duration = round($finishedAt->diffInMilliseconds($startedAt) / 1000, 2);

                        $run->update([
                            'status' => $exitCode === 0 ? 'success' : 'failed',
                            'finished_at' => $finishedAt,
                            'duration_seconds' => $duration,
                            'exit_code' => $exitCode,
                            'output' => $output,
                        ]);

                        Notification::make()
                            ->title($exitCode === 0 ? 'Task Completed' : 'Task Failed')
                            ->body($output ?: "Exit code {$exitCode} in {$duration}s")
                            ->color($exitCode === 0 ? 'success' : 'danger')
                            ->send();
                    } catch (\Throwable $e) {
                        $run->update([
                            'status' => 'failed',
                            'finished_at' => now(),
                            'exit_code' => 1,
                            'output' => $e->getMessage()."\n".$e->getTraceAsString(),
                        ]);

                        Notification::make()
                            ->title('Task Execution Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('triggerScheduler')
                ->label('Run Scheduler (Due Tasks)')
                ->icon('heroicon-m-bolt')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Run Due Scheduled Tasks')
                ->modalDescription('Invokes "schedule:run" to execute tasks whose clock minute is currently due.')
                ->action(function () {
                    try {
                        Artisan::call('schedule:run');
                        $output = trim(Artisan::output());

                        Notification::make()
                            ->title('Scheduler Executed')
                            ->body($output ?: 'Checked due tasks.')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Scheduler Error')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('pruneRuns')
                ->label('Prune Old Runs')
                ->icon('heroicon-m-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Prune Schedule Runs Older Than 2 Days')
                ->modalDescription('Are you sure you want to delete all schedule run entries whose run date is more than 2 days old?')
                ->action(function () {
                    try {
                        Artisan::call('schedule-runs:prune');
                        $output = trim(Artisan::output());

                        Notification::make()
                            ->title('Prune Completed')
                            ->body($output ?: 'Schedule run records older than 2 days were deleted.')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Prune Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function getScheduledTasks(): array
    {
        $schedule = app(Schedule::class);
        $tasks = [];

        foreach ($schedule->events() as $event) {
            $cmd = $event->command ?? null;
            if ($cmd) {
                $cleaned = trim(preg_replace('/^.*?artisan(?:\.php)?\s*/i', '', $cmd));
                // strip quotes or redirects
                $cleaned = preg_replace('/\s*>[^$]*/', '', $cleaned);
                $cleaned = trim($cleaned, " '\"");
                $tasks[$cleaned] = 'artisan '.$cleaned;
            } elseif ($event->description) {
                $tasks[$event->description] = $event->description;
            }
        }

        return $tasks ?: [
            'employees:sync' => 'artisan employees:sync',
            'biometrics:sync' => 'artisan biometrics:sync',
            'suspensions:check-status' => 'artisan suspensions:check-status',
            'transitions:apply-effective' => 'artisan transitions:apply-effective',
            'sync-logs:prune' => 'artisan sync-logs:prune',
            'schedule-runs:prune' => 'artisan schedule-runs:prune',
        ];
    }
}
