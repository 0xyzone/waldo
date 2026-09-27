<?php

namespace App\Filament\Resources\IdCardPrintReports\Pages;

use App\Filament\Resources\IdCardPrintReports\IdCardPrintReportResource;
use App\Services\IdCardPrintProcessingService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateIdCardPrintReport extends CreateRecord
{
    protected static string $resource = IdCardPrintReportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);

        if ($record->csv_file_path) {
            try {
                $count = app(IdCardPrintProcessingService::class)->processUploadedFile($record);

                Notification::make()
                    ->title('ID Card Batch Processed')
                    ->body("Successfully imported and matched {$count} employee records.")
                    ->success()
                    ->send();
            } catch (\Throwable $e) {
                Notification::make()
                    ->title('CSV Processing Notice')
                    ->body('Batch created, but encountered an error processing CSV: '.$e->getMessage())
                    ->warning()
                    ->send();
            }
        } else {
            Notification::make()
                ->title('ID Card Batch Created')
                ->body('Empty batch created. You can now add employees manually or import a CSV anytime.')
                ->success()
                ->send();
        }

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
