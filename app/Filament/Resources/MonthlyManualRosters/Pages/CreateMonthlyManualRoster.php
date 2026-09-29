<?php

namespace App\Filament\Resources\MonthlyManualRosters\Pages;

use App\Filament\Resources\MonthlyManualRosters\MonthlyManualRosterResource;
use App\Models\ManualAttendanceEmployee;
use App\Models\MonthlyManualRoster;
use App\Models\MonthlyManualRosterItem;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateMonthlyManualRoster extends CreateRecord
{
    protected static string $resource = MonthlyManualRosterResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $monthName = Carbon::create()->month((int) $data['month'])->format('F');
        $data['title'] = "{$monthName} {$data['year']} Manual Roster";
        $data['created_by'] = Auth::id();

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        /** @var MonthlyManualRoster $record */
        $record = parent::handleRecordCreation($data);

        // Fetch all active manual attendance employees
        $manualEmployees = ManualAttendanceEmployee::active()
            ->with(['employee.department', 'employee.designation'])
            ->get();

        $count = 0;
        foreach ($manualEmployees as $manualEmp) {
            $emp = $manualEmp->employee;
            $name = $emp?->name ?: $manualEmp->employee_code;
            $dept = $emp?->department?->name;
            $desig = $emp?->designation?->name;

            MonthlyManualRosterItem::create([
                'monthly_manual_roster_id' => $record->id,
                'employee_code' => $manualEmp->employee_code,
                'employee_name' => $name,
                'department' => $dept,
                'designation' => $desig,
                'is_roster_updated' => false,
            ]);
            $count++;
        }

        $record->refreshStatistics();

        Notification::make()
            ->title('Roster Checklist Initialized')
            ->body("{$record->period} created with {$count} manual attendance employee(s).")
            ->success()
            ->send();

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
