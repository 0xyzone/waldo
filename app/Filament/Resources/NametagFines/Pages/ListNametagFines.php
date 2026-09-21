<?php

namespace App\Filament\Resources\NametagFines\Pages;

use App\Filament\Resources\NametagFines\NametagFineResource;
use App\Models\NametagFine;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListNametagFines extends ListRecords
{
    protected static string $resource = NametagFineResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(NametagFine::count()),
            'pending_acknowledgement' => Tab::make('Pending Acknowledgment')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('acknowledged', false))
                ->badge(NametagFine::where('acknowledged', false)->count()),
            'acknowledged' => Tab::make('Acknowledged')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('acknowledged', true))
                ->badge(NametagFine::where('acknowledged', true)->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New NameTag Fine')
                ->icon('heroicon-o-plus'),
        ];
    }
}
