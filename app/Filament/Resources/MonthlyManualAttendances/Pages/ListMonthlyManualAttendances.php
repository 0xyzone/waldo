<?php

namespace App\Filament\Resources\MonthlyManualAttendances\Pages;

use App\Filament\Resources\MonthlyManualAttendances\MonthlyManualAttendanceResource;
use App\Models\MonthlyManualAttendance;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMonthlyManualAttendances extends ListRecords
{
    protected static string $resource = MonthlyManualAttendanceResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Attendances')
                ->badge(MonthlyManualAttendance::count()),
            'in_progress' => Tab::make('In Progress')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'in_progress'))
                ->badge(MonthlyManualAttendance::where('status', 'in_progress')->count()),
            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'completed'))
                ->badge(MonthlyManualAttendance::where('status', 'completed')->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Monthly Attendance')
                ->icon('heroicon-m-plus'),
        ];
    }
}
