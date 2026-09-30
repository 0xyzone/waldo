<?php

namespace App\Filament\Resources\SalaryIncrementRequests\Pages;

use App\Filament\Resources\SalaryIncrementRequests\SalaryIncrementRequestResource;
use App\Models\SalaryIncrementRequest;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListSalaryIncrementRequests extends ListRecords
{
    protected static string $resource = SalaryIncrementRequestResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(SalaryIncrementRequest::count()),

            'pending_hr' => Tab::make('Pending HR Ack')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('hr_acknowledged', false))
                ->badge(SalaryIncrementRequest::where('hr_acknowledged', false)->count()),

            'pending_finance' => Tab::make('Pending Finance Ack')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('finance_acknowledged', false))
                ->badge(SalaryIncrementRequest::where('finance_acknowledged', false)->count()),

            'hr_acknowledged' => Tab::make('HR Acknowledged')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('hr_acknowledged', true))
                ->badge(SalaryIncrementRequest::where('hr_acknowledged', true)->count()),

            'finance_acknowledged' => Tab::make('Finance Acknowledged')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('finance_acknowledged', true))
                ->badge(SalaryIncrementRequest::where('finance_acknowledged', true)->count()),

            'approved' => Tab::make('Approved')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved'))
                ->badge(SalaryIncrementRequest::where('status', 'approved')->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('printBlankForm')
                ->label('Print Blank Form (2x A5)')
                ->icon('heroicon-m-printer')
                ->color('success')
                ->url(route('salary-increment-requests.print-blank'))
                ->openUrlInNewTab(),

            CreateAction::make()
                ->label('New Increment Request')
                ->icon('heroicon-m-plus')
                ->modalWidth('3xl')
                ->mutateFormDataUsing(function (array $data): array {
                    $data['created_by'] = Auth::id();

                    return $data;
                }),
        ];
    }
}
