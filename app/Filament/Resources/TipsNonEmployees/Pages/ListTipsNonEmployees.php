<?php

namespace App\Filament\Resources\TipsNonEmployees\Pages;

use App\Filament\Resources\TipsNonEmployees\TipsNonEmployeeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTipsNonEmployees extends ListRecords
{
    protected static string $resource = TipsNonEmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Non-Employee')
                ->icon('heroicon-m-plus'),
        ];
    }
}
