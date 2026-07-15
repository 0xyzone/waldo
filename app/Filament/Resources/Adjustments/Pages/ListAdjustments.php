<?php

namespace App\Filament\Resources\Adjustments\Pages;

use App\Filament\Resources\Adjustments\AdjustmentResource;
use App\Models\Adjustment;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAdjustments extends ListRecords
{
    protected static string $resource = AdjustmentResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
            ->badge(Adjustment::count()),
            'add' => Tab::make('Additions')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'add'))
                ->badge(Adjustment::where('type', 'add')->count()),
            'subtract' => Tab::make('Deductions')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'subtract'))
                ->badge(Adjustment::where('type', 'subtract')->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
