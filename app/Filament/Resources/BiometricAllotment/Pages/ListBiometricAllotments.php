<?php

namespace App\Filament\Resources\BiometricAllotment\Pages;

use App\Filament\Resources\BiometricAllotmentResource;
use App\Models\BiometricAllotment;
use App\Services\BiometricSheetsService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBiometricAllotments extends ListRecords
{
    protected static string $resource = BiometricAllotmentResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
            ->badge(BiometricAllotment::count()),
            'done' => Tab::make('Done')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Done'))
                ->badge(BiometricAllotment::where('status', 'Done')->count()),
            'left_job' => Tab::make('Left Job')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Left Job'))
                ->badge(BiometricAllotment::where('status', 'Left Job')->count()),
            'not_done_yet' => Tab::make('Not Done Yet')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Not Done Yet'))
                ->badge(BiometricAllotment::where('status', 'Not Done Yet')->count()),
            'bio_not_required' => Tab::make('Bio Not Required')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'Bio Not Required'))
                ->badge(BiometricAllotment::where('status', 'Bio Not Required')->count()),
        ];
    }

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
