<?php

namespace App\Filament\Resources\NametagReturns\Pages;

use App\Filament\Resources\NametagReturns\NametagReturnResource;
use App\Models\NametagReturn;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListNametagReturns extends ListRecords
{
    protected static string $resource = NametagReturnResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Returns')
                ->badge(NametagReturn::count()),
            'good' => Tab::make('Good Condition')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('condition', 'Good'))
                ->badge(NametagReturn::where('condition', 'Good')->count()),
            'damaged' => Tab::make('Damaged')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('condition', 'Damaged'))
                ->badge(NametagReturn::where('condition', 'Damaged')->count()),
            'lost' => Tab::make('Lost / Missing')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('condition', 'Lost'))
                ->badge(NametagReturn::where('condition', 'Lost')->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Record NameTag Return')
                ->icon('heroicon-o-plus')
                ->mutateFormDataUsing(function (array $data) {
                    $data['received_by'] = auth()->id();

                    return $data;
                }),
        ];
    }
}
