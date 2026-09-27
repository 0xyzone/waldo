<?php

namespace App\Filament\Resources\IdCardPrintReports\Pages;

use App\Filament\Resources\IdCardPrintReports\IdCardPrintReportResource;
use App\Services\IdCardPrintProcessingService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditIdCardPrintReport extends EditRecord
{
    protected static string $resource = IdCardPrintReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reprocess')
                ->label('Re-import CSV')
                ->icon('heroicon-m-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Re-import CSV Data')
                ->modalDescription('This will re-read the uploaded CSV file and refresh all ID card items. Existing card status changes might be reset to "printed". Proceed?')
                ->action(function () {
                    try {
                        $count = app(IdCardPrintProcessingService::class)->processUploadedFile($this->record);
                        Notification::make()
                            ->title('Batch Re-imported')
                            ->body("Successfully re-imported {$count} employee records.")
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Error')
                            ->body('Failed to re-import CSV: '.$e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
