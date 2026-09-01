<?php

namespace App\Filament\Resources\Candidates\Pages;

use App\Filament\Resources\Candidates\CandidateResource;
use App\Models\Candidate;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ListCandidates extends ListRecords
{
    protected static string $resource = CandidateResource::class;

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
                ->badge(Candidate::count()),
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'pending'))
                ->badge(Candidate::query()->where('status', 'pending')->count()),
            'contacted' => Tab::make('Contacted')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'contacted'))
                ->badge(Candidate::query()->where('status', 'contacted')->count()),
            'unreachable' => Tab::make('Unreachable')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'unreachable'))
                ->badge(Candidate::query()->where('status', 'unreachable')->count()),
            'not_coming' => Tab::make('Not Coming')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'not_coming'))
                ->badge(Candidate::query()->where('status', 'not_coming')->count()),
            'approved' => Tab::make('Approved')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'approved'))
                ->badge(Candidate::query()->where('status', 'approved')->count()),
            'no_show' => Tab::make('No Show')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'no_show'))
                ->badge(Candidate::query()->where('status', 'no_show')->count()),
            'rejected' => Tab::make('Rejected')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'rejected'))
                ->badge(Candidate::query()->where('status', 'rejected')->count()),
        ];
    }
}
