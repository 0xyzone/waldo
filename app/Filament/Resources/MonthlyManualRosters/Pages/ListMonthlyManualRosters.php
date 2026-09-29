<?php

namespace App\Filament\Resources\MonthlyManualRosters\Pages;

use App\Filament\Resources\MonthlyManualRosters\MonthlyManualRosterResource;
use App\Models\MonthlyManualRoster;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMonthlyManualRosters extends ListRecords
{
    protected static string $resource = MonthlyManualRosterResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Rosters')
                ->badge(MonthlyManualRoster::count()),
            'in_progress' => Tab::make('In Progress')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'in_progress'))
                ->badge(MonthlyManualRoster::where('status', 'in_progress')->count()),
            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'completed'))
                ->badge(MonthlyManualRoster::where('status', 'completed')->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Monthly Roster')
                ->icon('heroicon-m-plus'),
        ];
    }
}
