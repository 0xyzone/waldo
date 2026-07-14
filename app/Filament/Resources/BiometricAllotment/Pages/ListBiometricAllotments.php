<?php

namespace App\Filament\Resources\BiometricAllotment\Pages;

use App\Filament\Resources\BiometricAllotmentResource;
use App\Services\BiometricSheetsService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListBiometricAllotments extends ListRecords
{
    protected static string $resource = BiometricAllotmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync')
                ->label('Sync Biometrics')
                ->color('success')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalHeading('Sync Biometrics from Google Sheet')
                ->modalDescription('Are you sure you want to pull data from the Google Sheet? This will update existing allotments and create new ones.')
                ->action(function (BiometricSheetsService $syncService): void {
                    try {
                        $count = $syncService->sync();

                        Notification::make()
                            ->title('Sync Completed')
                            ->body("Successfully synced {$count} biometric records from Google Sheet!")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Sync Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            CreateAction::make(),
            Action::make('Visit Sheet')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('info')
                ->url('https://docs.google.com/spreadsheets/d/15yIpkm8tXifnXvhPC1kw8GCxO2aWzl12NNKswqJ_fts/edit?gid=1421637106#gid=1421637106', true)
                ->openUrlInNewTab(),
        ];
    }
}
