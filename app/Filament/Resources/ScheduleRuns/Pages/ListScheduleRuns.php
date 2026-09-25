<?php

namespace App\Filament\Resources\ScheduleRuns\Pages;

use App\Filament\Resources\ScheduleRuns\ScheduleRunResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;

class ListScheduleRuns extends ListRecords
{
    protected static string $resource = ScheduleRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('triggerScheduler')
                ->label('Run Scheduler Now')
                ->icon('heroicon-m-bolt')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Run Due Scheduled Tasks')
                ->modalDescription('This will invoke "schedule:run" to execute all tasks currently due.')
                ->action(function () {
                    try {
                        Artisan::call('schedule:run');
                        $output = trim(Artisan::output());

                        Notification::make()
                            ->title('Scheduler Executed')
                            ->body($output ?: 'Due tasks executed or checked.')
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
        ];
    }
}
