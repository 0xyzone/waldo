<?php

namespace App\Filament\Resources\TipsAdjustments\Pages;

use App\Filament\Resources\TipsAdjustments\TipsAdjustmentResource;
use App\Models\TipsAdjustment;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ListTipsAdjustments extends ListRecords
{
    protected static string $resource = TipsAdjustmentResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(TipsAdjustment::count()),
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'pending'))
                ->badge(TipsAdjustment::where('status', 'pending')->count())
                ->badgeColor('warning'),
            'updated' => Tab::make('Updated')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'updated'))
                ->badge(TipsAdjustment::where('status', 'updated')->count())
                ->badgeColor('success'),
            'cancelled' => Tab::make('Cancelled')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'cancelled'))
                ->badge(TipsAdjustment::where('status', 'cancelled')->count())
                ->badgeColor('gray'),
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
