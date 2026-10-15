<?php

namespace App\Filament\Resources\EmployeePromotions\Pages;

use App\Filament\Resources\EmployeePromotions\EmployeePromotionResource;
use App\Models\Employee;
use App\Models\EmployeePromotion;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ManageEmployeePromotions extends ManageRecords
{
    protected static string $resource = EmployeePromotionResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(EmployeePromotion::count()),
            'hrms_synced' => Tab::make('HRMS Synced')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('hrms_synced', true))
                ->badge(EmployeePromotion::where('hrms_synced', true)->count())
                ->badgeColor('info'),
            'hrms_not_synced' => Tab::make('HRMS Not Synced')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('hrms_synced', false))
                ->badge(EmployeePromotion::where('hrms_synced', false)->count())
                ->badgeColor('warning'),
            'acknowledged' => Tab::make('Acknowledged')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('acknowledged', true))
                ->badge(EmployeePromotion::where('acknowledged', true)->count())
                ->badgeColor('success'),
            'not_acknowledged' => Tab::make('Not Acknowledged')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('acknowledged', false))
                ->badge(EmployeePromotion::where('acknowledged', false)->count())
                ->badgeColor('gray'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth('4xl'),
        ];
    }

    /**
     * Capture the employee's current department and designation as the "from" snapshot.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $employee = Employee::where('employee_code', $data['employee_id'])->first();

        if ($employee) {
            $data['from_department_id'] = $employee->department_id;
            $data['from_designation_id'] = $employee->designation_id;
        }

        return $data;
    }
}
