<?php

namespace App\Filament\Resources\Leavers\Pages;

use App\Filament\Resources\Leavers\LeaverResource;
use App\Models\Leaver;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListLeavers extends ListRecords
{
    protected static string $resource = LeaverResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Leavers')
                ->badge(Leaver::count()),
            'offboarded' => Tab::make('Offboarded')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('offboarded', true))
                ->badge(Leaver::query()->where('offboarded', true)->count()),
            'not_offboarded' => Tab::make('Not Offboarded')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('offboarded', false))
                ->badge(Leaver::where('offboarded', false)->count()),
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'pending'))
                ->badge(Leaver::where('status', 'pending')->count()),
            'cleared' => Tab::make('Cleared')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'cleared'))
                ->badge(Leaver::where('status', 'cleared')->count()),
            'cancelled' => Tab::make('Cancelled')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'cancelled'))
                ->badge(Leaver::where('status', 'cancelled')->count()),
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
