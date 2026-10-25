<?php

namespace App\Filament\Resources\MonthlyManualAttendances\Pages;

use App\Filament\Resources\MonthlyManualAttendances\MonthlyManualAttendanceResource;
use App\Models\ManualAttendanceStaff;
use App\Models\MonthlyManualAttendance;
use App\Models\MonthlyManualAttendanceItem;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateMonthlyManualAttendance extends CreateRecord
{
    protected static string $resource = MonthlyManualAttendanceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $monthName = Carbon::create()->month((int) $data['month'])->format('F');
        $data['title'] = "{$monthName} {$data['year']} Manual Attendance";
        $data['created_by'] = Auth::id();

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        /** @var MonthlyManualAttendance $record */
        $record = parent::handleRecordCreation($data);

        // Fetch all active manual attendance staff
        $manualStaff = ManualAttendanceStaff::active()
            ->with(['employee.department', 'employee.designation'])
            ->get();

        $count = 0;
        foreach ($manualStaff as $staff) {
            $emp = $staff->employee;
            $name = $emp?->name ?: $staff->employee_code;
            $dept = $emp?->department?->name;
            $desig = $emp?->designation?->name;

            MonthlyManualAttendanceItem::create([
                'monthly_manual_attendance_id' => $record->id,
                'employee_code' => $staff->employee_code,
                'employee_name' => $name,
                'department' => $dept,
                'designation' => $desig,
                'is_attendance_updated' => false,
            ]);
            $count++;
        }

        $record->refreshStatistics();

        Notification::make()
            ->title('Attendance Checklist Initialized')
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
