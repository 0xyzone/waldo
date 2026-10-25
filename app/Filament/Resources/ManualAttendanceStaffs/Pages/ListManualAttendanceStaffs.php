<?php

namespace App\Filament\Resources\ManualAttendanceStaffs\Pages;

use App\Filament\Resources\ManualAttendanceStaffs\ManualAttendanceStaffResource;
use App\Models\ManualAttendanceStaff;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListManualAttendanceStaffs extends ListRecords
{
    protected static string $resource = ManualAttendanceStaffResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Staff')
                ->badge(ManualAttendanceStaff::count()),
            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('is_active', true))
                ->badge(ManualAttendanceStaff::where('is_active', true)->count()),
            'inactive' => Tab::make('Inactive')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('is_active', false))
                ->badge(ManualAttendanceStaff::where('is_active', false)->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Designate Staff')
                ->icon('heroicon-m-user-plus'),
        ];
    }
}
