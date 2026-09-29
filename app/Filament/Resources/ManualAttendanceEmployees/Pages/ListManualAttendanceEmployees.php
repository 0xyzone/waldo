<?php

namespace App\Filament\Resources\ManualAttendanceEmployees\Pages;

use App\Filament\Resources\ManualAttendanceEmployees\ManualAttendanceEmployeeResource;
use App\Models\ManualAttendanceEmployee;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListManualAttendanceEmployees extends ListRecords
{
    protected static string $resource = ManualAttendanceEmployeeResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(ManualAttendanceEmployee::count()),
            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('is_active', true))
                ->badge(ManualAttendanceEmployee::where('is_active', true)->count()),
            'inactive' => Tab::make('Inactive')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('is_active', false))
                ->badge(ManualAttendanceEmployee::where('is_active', false)->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Employee')
                ->icon('heroicon-m-plus'),
        ];
    }
}
