<?php

namespace App\Filament\Resources\NametagDistributions\Pages;

use App\Filament\Resources\NametagDistributions\NametagDistributionResource;
use App\Models\NametagDistribution;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListNametagDistributions extends ListRecords
{
    protected static string $resource = NametagDistributionResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(NametagDistribution::count()),
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Pending'))
                ->badge(NametagDistribution::where('status', 'Pending')->count()),
            'printed' => Tab::make('Printed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Printed'))
                ->badge(NametagDistribution::where('status', 'Printed')->count()),
            'released' => Tab::make('Released')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Released'))
                ->badge(NametagDistribution::where('status', 'Released')->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New NameTag Record')
                ->icon('heroicon-o-plus'),
        ];
    }
}
