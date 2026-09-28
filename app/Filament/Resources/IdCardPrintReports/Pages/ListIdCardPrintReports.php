<?php

namespace App\Filament\Resources\IdCardPrintReports\Pages;

use App\Filament\Resources\IdCardPrintReports\IdCardPrintReportResource;
use App\Models\IdCardPrintReport;
use App\Services\IdCardPrintProcessingService;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListIdCardPrintReports extends ListRecords
{
    protected static string $resource = IdCardPrintReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('6xl')
                ->slideOver()
                ->label('New ID Card Batch')
                ->icon('heroicon-m-plus')
                ->mutateFormDataUsing(function (array $data): array {
                    $data['created_by'] = auth()->id();

                    return $data;
                })
                ->after(function (IdCardPrintReport $record) {
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
                                ->body('Batch created, but error processing CSV: '.$e->getMessage())
                                ->warning()
                                ->send();
                        }
                    }
                }),
        ];
    }
}
