<?php

namespace App\Filament\Resources\IdCardRequests\Pages;

use App\Filament\Resources\IdCardRequests\IdCardRequestResource;
use App\Models\IdCardRequest;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListIdCardRequests extends ListRecords
{
    protected static string $resource = IdCardRequestResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(IdCardRequest::count()),
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(IdCardRequest::where('status', 'pending')->count()),
            'designed' => Tab::make('Designed')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'designed'))
                ->badge(IdCardRequest::where('status', 'designed')->count()),
            'sent_for_print' => Tab::make('Sent for Print')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'sent for print'))
                ->badge(IdCardRequest::where('status', 'sent for print')->count()),
            'done' => Tab::make('Done')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'done'))
                ->badge(IdCardRequest::where('status', 'done')->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
