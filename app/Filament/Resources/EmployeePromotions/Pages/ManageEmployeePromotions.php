<?php

namespace App\Filament\Resources\EmployeePromotions\Pages;

use App\Filament\Resources\EmployeePromotions\EmployeePromotionResource;
use App\Models\Employee;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageEmployeePromotions extends ManageRecords
{
    protected static string $resource = EmployeePromotionResource::class;

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
