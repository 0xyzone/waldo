<?php

namespace App\Filament\Resources\TipsYearlyEomGomReports\Pages;

use App\Filament\Resources\TipsYearlyEomGomReports\TipsYearlyEomGomReportResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateTipsYearlyEomGomReport extends CreateRecord
{
    protected static string $resource = TipsYearlyEomGomReportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);

        // Generate 12 months with 3 entries per month automatically
        $record->generateEntries();

        Notification::make()
            ->title('Yearly EOM & GOM Report Generated')
            ->body("Successfully generated all 12 months for {$record->year} with Gaming/Slot and randomly assigned allowed departments.")
            ->success()
            ->send();

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
