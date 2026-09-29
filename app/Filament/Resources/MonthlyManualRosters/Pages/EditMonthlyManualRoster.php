<?php

namespace App\Filament\Resources\MonthlyManualRosters\Pages;

use App\Filament\Resources\MonthlyManualRosters\MonthlyManualRosterResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMonthlyManualRoster extends EditRecord
{
    protected static string $resource = MonthlyManualRosterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open_checklist')
                ->label('Open Checklist')
                ->icon('heroicon-m-clipboard-document-check')
                ->color('primary')
                ->url(fn (): string => $this->getResource()::getUrl('view', ['record' => $this->getRecord()])),

            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
