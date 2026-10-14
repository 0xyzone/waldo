<?php

namespace App\Filament\Resources\TerminatedEmployees\Pages;

use App\Filament\Resources\TerminatedEmployeeResource;
use App\Models\TerminatedEmployee;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTerminatedEmployees extends ListRecords
{
    protected static string $resource = TerminatedEmployeeResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Terminated')
                ->badge(TerminatedEmployee::count()),
            'letter_pending' => Tab::make('Pending Print')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_letter_printed', false))
                ->badge(TerminatedEmployee::where('is_letter_printed', false)->count())
                ->badgeColor('warning'),
            'letter_printed' => Tab::make('Letter Printed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_letter_printed', true)->where('is_letter_released', false))
                ->badge(TerminatedEmployee::where('is_letter_printed', true)->where('is_letter_released', false)->count())
                ->badgeColor('info'),
            'letter_released' => Tab::make('Letter Released')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_letter_released', true))
                ->badge(TerminatedEmployee::where('is_letter_released', true)->count())
                ->badgeColor('success'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('6xl'),
        ];
    }
}
