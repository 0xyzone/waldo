<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Services\GoogleSheetsService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployee extends ViewRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Action::make('syncToGoogleSheet')
            //     ->label('Sync to Google Sheet')
            //     ->icon('heroicon-o-arrow-path')
            //     ->color('success')
            //     ->action(function (GoogleSheetsService $service) {
            //         $service->syncEmployee($this->record);

            //         Notification::make()
            //             ->title('Synced successfully')
            //             ->body("Employee {$this->record->employee_code} was successfully synced to Google Sheets.")
            //             ->success()
            //             ->send();
            //     }),
        ];
    }
}
