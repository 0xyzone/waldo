<?php

namespace App\Filament\Resources\RoyaltyReports\Pages;

use App\Filament\Resources\RoyaltyReports\RoyaltyReportResource;
use App\Services\RoyaltyCalculationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditRoyaltyReport extends EditRecord
{
    protected static string $resource = RoyaltyReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('recalculate')
                ->label('Recalculate Payouts')
                ->icon('heroicon-m-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Recalculate Royalty Report')
                ->modalDescription('This will re-evaluate all attendance and performance deductions and update payouts. Proceed?')
                ->action(function () {
                    app(RoyaltyCalculationService::class)->generate($this->record);

                    Notification::make()
                        ->title('Report Recalculated')
                        ->body('All employee bonus items have been recalculated.')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
