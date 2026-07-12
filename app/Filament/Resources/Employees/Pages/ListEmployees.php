<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\Employee;
use App\Services\EmployeeSyncService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Employees'),
            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('employee_status', 'Active'))
                ->badge(Employee::where('employee_status', 'Active')->count()),
            'inactive' => Tab::make('Inactive')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('employee_status', 'Inactive'))
                ->badge(Employee::where('employee_status', 'Inactive')->count()),
            'resigning_this_month' => Tab::make('Resigning This Month')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('employee_status', 'Resigning this month'))
                ->badge(Employee::where('employee_status', 'Resigning this month')->count()),
            'resigned' => Tab::make('Resigned')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('employee_status', 'Resigned'))
                ->badge(Employee::where('employee_status', 'Resigned')->count()),
            'terminated' => Tab::make('Terminated')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('employee_status', 'Terminated'))
                ->badge(Employee::where('employee_status', 'Terminated')->count()),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'active';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync')
                ->label('Sync Employees')
                ->color('success')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalHeading('Sync Employees from Google Sheet')
                ->modalDescription('Are you sure you want to pull data from the Google Sheet? This will update existing employees and create new ones.')
                ->action(function (EmployeeSyncService $syncService): void {
                    try {
                        $count = $syncService->sync();

                        Notification::make()
                            ->title('Sync Completed')
                            ->body("Successfully synced {$count} employees from Google Sheet!")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Sync Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            CreateAction::make(),
            Action::make('Visit Sheet')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('info')
                ->url('https://docs.google.com/spreadsheets/d/1i8M_P8KejphnCEFpiUWIhaTvkDZtGqt1_AuWt-vNJGE/edit?gid=0#gid=0', true)
                ->openUrlInNewTab(),
        ];
    }
}
