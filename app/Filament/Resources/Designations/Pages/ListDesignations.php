<?php

namespace App\Filament\Resources\Designations\Pages;

use App\Filament\Resources\Designations\DesignationResource;
use App\Models\Designation;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListDesignations extends ListRecords
{
    protected static string $resource = DesignationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(Designation::count()),
            'is_active' => Tab::make('Is Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(Designation::where('is_active', true)->count())
                ->badgeColor('success'),
            'is_inactive' => Tab::make('Is Inactive')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(Designation::where('is_active', false)->count())
                ->badgeColor('danger'),
            'has_description' => Tab::make('Has Description')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('job_description')->where('job_description', '!=', ''))
                ->badge(Designation::whereNotNull('job_description')->where('job_description', '!=', '')->count())
                ->badgeColor('info'),
            'doesnt_have_description' => Tab::make("Doesn't Have Description")
                ->modifyQueryUsing(fn (Builder $query) => $query->where(fn (Builder $q) => $q->whereNull('job_description')->orWhere('job_description', '')))
                ->badge(Designation::where(fn (Builder $q) => $q->whereNull('job_description')->orWhere('job_description', ''))->count())
                ->badgeColor('gray'),
        ];
    }
}
