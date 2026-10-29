<?php

namespace App\Filament\Resources\RoyaltyReports\Pages;

use App\Filament\Resources\RoyaltyReports\RoyaltyReportResource;
use App\Services\RoyaltyCalculationService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateRoyaltyReport extends CreateRecord
{
    protected static string $resource = RoyaltyReportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['status'] = 'draft';

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);

        try {
            app(RoyaltyCalculationService::class)->generate($record);

            Notification::make()
                ->title('Royalty Report Generated')
                ->body("Successfully generated {$record->items()->count()} staff royalty bonus records.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Generation Error')
                ->body('Report created, but calculation encountered an issue: '.$e->getMessage())
                ->warning()
                ->send();
        }

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
