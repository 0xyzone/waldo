<?php

namespace Tests\Unit;

use App\Filament\Resources\MonthlyManualAttendances\Pages\ListMonthlyManualAttendances;
use App\Filament\Resources\MonthlyManualAttendances\Tables\MonthlyManualAttendancesTable;
use App\Filament\Resources\MonthlyManualRosters\Pages\ListMonthlyManualRosters;
use App\Filament\Resources\MonthlyManualRosters\Tables\MonthlyManualRostersTable;
use App\Models\Employee;
use App\Models\ManualAttendanceEmployee;
use App\Models\MonthlyManualAttendance;
use App\Models\MonthlyManualRoster;
use App\Models\MonthlyManualRosterItem;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Table;
use Tests\TestCase;

class ManualAttendanceAndRosterTest extends TestCase
{
    public function test_manual_attendance_employee_has_correct_fillable_and_casts(): void
    {
        $model = new ManualAttendanceEmployee;

        $this->assertEquals('manual_attendance_employees', $model->getTable());
        $this->assertContains('employee_code', $model->getFillable());
        $this->assertContains('is_active', $model->getFillable());
        $this->assertContains('notes', $model->getFillable());
        $this->assertContains('created_by', $model->getFillable());
        $this->assertEquals('boolean', $model->getCasts()['is_active'] ?? null);
    }

    public function test_monthly_manual_roster_has_correct_fillable_and_computed_properties(): void
    {
        $roster = new MonthlyManualRoster([
            'year' => 2026,
            'month' => 9,
            'total_employees' => 20,
            'completed_count' => 15,
        ]);

        $this->assertEquals('monthly_manual_rosters', $roster->getTable());
        $this->assertEquals('September', $roster->month_name);
        $this->assertEquals('September 2026', $roster->period);
        $this->assertEquals(5, $roster->pending_count);
        $this->assertEquals(75, $roster->completion_percentage);
    }

    public function test_monthly_manual_roster_zero_employees_returns_zero_percent(): void
    {
        $roster = new MonthlyManualRoster([
            'year' => 2026,
            'month' => 10,
            'total_employees' => 0,
            'completed_count' => 0,
        ]);

        $this->assertEquals('October 2026', $roster->period);
        $this->assertEquals(0, $roster->completion_percentage);
        $this->assertEquals(0, $roster->pending_count);
    }

    public function test_monthly_manual_roster_item_has_correct_fillable_and_casts(): void
    {
        $item = new MonthlyManualRosterItem;

        $this->assertEquals('monthly_manual_roster_items', $item->getTable());
        $this->assertContains('monthly_manual_roster_id', $item->getFillable());
        $this->assertContains('employee_code', $item->getFillable());
        $this->assertContains('employee_name', $item->getFillable());
        $this->assertContains('department', $item->getFillable());
        $this->assertContains('designation', $item->getFillable());
        $this->assertContains('is_roster_updated', $item->getFillable());
        $this->assertContains('updated_at_hrms', $item->getFillable());
        $this->assertContains('updated_by', $item->getFillable());
        $this->assertContains('notes', $item->getFillable());
        $this->assertEquals('boolean', $item->getCasts()['is_roster_updated'] ?? null);
    }

    public function test_employee_model_has_manual_attendance_relationship(): void
    {
        $employee = new Employee;
        $this->assertTrue(method_exists($employee, 'manualAttendance'));
    }

    public function test_checkbox_columns_are_center_aligned(): void
    {
        $attendanceCol = CheckboxColumn::make('is_attendance_updated')->alignCenter();
        $this->assertEquals(Alignment::Center, $attendanceCol->getAlignment());

        $rosterCol = CheckboxColumn::make('is_roster_updated')->alignCenter();
        $this->assertEquals(Alignment::Center, $rosterCol->getAlignment());
    }

    public function test_global_create_and_edit_actions_configured_as_slideover_7xl(): void
    {
        $create = CreateAction::make();
        $this->assertTrue($create->isModalSlideOver());
        $this->assertEquals(Width::SevenExtraLarge, $create->getModalWidth());
        $this->assertTrue($create->hasModal());

        $edit = EditAction::make();
        $this->assertTrue($edit->isModalSlideOver());
        $this->assertEquals(Width::SevenExtraLarge, $edit->getModalWidth());
        $this->assertTrue($edit->hasModal());
    }

    public function test_manual_attendance_and_roster_table_row_click_opens_checklist(): void
    {
        $attendancesTable = Table::make((new ListMonthlyManualAttendances));
        MonthlyManualAttendancesTable::configure($attendancesTable);
        $attendanceRecord = new MonthlyManualAttendance(['id' => 123]);
        $this->assertEquals('view_checklist', $attendancesTable->getRecordAction($attendanceRecord));
        $this->assertStringContainsString('/kamkaj/monthly-manual-attendances/123', $attendancesTable->getRecordUrl($attendanceRecord));

        $rostersTable = Table::make((new ListMonthlyManualRosters));
        MonthlyManualRostersTable::configure($rostersTable);
        $rosterRecord = new MonthlyManualRoster(['id' => 456]);
        $this->assertEquals('view_checklist', $rostersTable->getRecordAction($rosterRecord));
        $this->assertStringContainsString('/kamkaj/monthly-manual-rosters/456', $rostersTable->getRecordUrl($rosterRecord));
    }

    public function test_table_record_action_defaults_to_view_or_edit_action(): void
    {
        $record = new MonthlyManualAttendance(['id' => 1]);

        $tableWithView = Table::make((new ListMonthlyManualAttendances));
        $tableWithView->actions([
            ViewAction::make(),
            EditAction::make(),
        ]);
        $this->assertEquals('view', $tableWithView->getRecordAction($record));

        $tableWithEdit = Table::make((new ListMonthlyManualAttendances));
        $tableWithEdit->actions([
            EditAction::make(),
        ]);
        $this->assertEquals('edit', $tableWithEdit->getRecordAction($record));
    }
}
