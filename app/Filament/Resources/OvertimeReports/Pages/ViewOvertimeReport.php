<?php

namespace App\Filament\Resources\OvertimeReports\Pages;

use App\Filament\Resources\OvertimeReports\OvertimeReportResource;
use App\Models\Department;
use App\Models\Employee;
use App\Models\OtBlock;
use App\Models\OtSpecialDate;
use App\Models\OvertimeAdjustment;
use App\Models\OvertimeEntry;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Collection;

class ViewOvertimeReport extends ViewRecord
{
    protected static string $resource = OvertimeReportResource::class;

    protected string $view = 'filament.resources.overtime-reports.pages.view-overtime-report';

    public string $search = '';

    public string $departmentId = '';

    public string $blockId = '';

    public string $otFilter = 'all';

    public ?int $activeBlockId = null;

    public int $page = 1;

    public int $perPage = 50;

    public ?string $editingEmployeeCode = null;

    public ?string $editingEmployeeName = null;

    public ?int $editingDay = null;

    public ?int $newBlockId = null;

    public string $newHours = '';

    public ?string $adjEmployeeCode = null;

    public ?string $adjEmployeeName = null;

    public ?string $adjType = null;

    public string $adjDate = '';

    public ?int $adjBlockId = null;

    public string $adjHours = '';

    public string $adjRemarks = '';

    public function canEnterOtValues(): bool
    {
        return OvertimeReportResource::canShield('EnterOtValues');
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);
        $this->activeBlockId = $this->getBlocks()->first()?->id;
    }

    public function saveInlineCell(string $employeeCode, int $day, string $value): void
    {
        if (! $this->canEnterOtValues() || ! $this->activeBlockId) {
            return;
        }

        $trimmed = trim($value);
        if ($trimmed === '' || (float) $trimmed <= 0) {
            return;
        }

        $hours = min(24.0, (float) $trimmed);
        $date = Carbon::create($this->record->year, $this->record->month, $day)->toDateString();

        OvertimeEntry::create([
            'employee_id' => $employeeCode,
            'ot_date' => $date,
            'ot_block_id' => $this->activeBlockId,
            'hours' => $hours,
        ]);
    }

    public function updatedSearch(): void
    {
        $this->page = 1;
    }

    public function updatedDepartmentId(): void
    {
        $this->page = 1;
    }

    public function updatedBlockId(): void
    {
        $this->page = 1;
    }

    public function updatedOtFilter(): void
    {
        $this->page = 1;
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->departmentId = '';
        $this->blockId = '';
        $this->otFilter = 'all';
        $this->page = 1;
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function nextPage(): void
    {
        $this->page++;
    }

    public function getTitle(): string
    {
        return 'OT Sheet — '.Carbon::create($this->record->year, $this->record->month, 1)->format('F Y');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('printBlockwise')
                ->label('Print Block-wise OT')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn () => route('overtime.reports.print-blockwise', $this->record), shouldOpenInNewTab: true),
            Action::make('exportExcel')
                ->label('Download Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('overtime.reports.export-excel', $this->record)),
        ];
    }

    public function getDaysInMonth(): int
    {
        return Carbon::create($this->record->year, $this->record->month, 1)->daysInMonth;
    }

    public function getSpecialDays(): array
    {
        return OtSpecialDate::whereMonth('date', $this->record->month)
            ->whereYear('date', $this->record->year)
            ->pluck('date')
            ->map(fn ($d) => Carbon::parse($d)->day)
            ->toArray();
    }

    public function getBlocks(): Collection
    {
        return OtBlock::where('is_active', true)->orderBy('sort_order')->get();
    }

    public function getDepartments(): Collection
    {
        return Department::orderBy('name')->get(['id', 'name']);
    }

    public function getEmployeesData(): array
    {
        $query = Employee::with('department:id,name')
            ->select(['employee_code', 'name', 'department_id'])
            // ->where('employee_status', 'active')
            ->orderByRaw("CAST(REPLACE(UPPER(employee_code), 'CWD', '') AS UNSIGNED) ASC")
            ->orderBy('employee_code');

        if (filled($this->search)) {
            $raw = trim($this->search);
            $term = '%'.$raw.'%';
            $query->where(function ($q) use ($term, $raw): void {
                $q->where('employee_code', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhereHas('department', fn ($dq) => $dq->where('name', 'like', $term));

                if (ctype_digit($raw)) {
                    $q->orWhere('employee_code', 'like', '%CWD'.$raw.'%');
                }
            });
        }

        if (filled($this->departmentId)) {
            $query->where('department_id', (int) $this->departmentId);
        }

        if (filled($this->blockId)) {
            $empCodesWithBlock = OvertimeEntry::whereMonth('ot_date', $this->record->month)
                ->whereYear('ot_date', $this->record->year)
                ->where('ot_block_id', (int) $this->blockId)
                ->distinct()
                ->pluck('employee_id');

            $empCodesWithAdjBlock = OvertimeAdjustment::where('overtime_report_id', $this->record->id)
                ->where('ot_block_id', (int) $this->blockId)
                ->distinct()
                ->pluck('employee_id');

            $query->whereIn('employee_code', $empCodesWithBlock->merge($empCodesWithAdjBlock)->unique()->toArray());
        }

        if ($this->otFilter !== 'all') {
            $empCodesWithOt = OvertimeEntry::whereMonth('ot_date', $this->record->month)
                ->whereYear('ot_date', $this->record->year)
                ->distinct()
                ->pluck('employee_id');

            $empCodesWithAdj = OvertimeAdjustment::where('overtime_report_id', $this->record->id)
                ->distinct()
                ->pluck('employee_id');

            $mergedOtCodes = $empCodesWithOt->merge($empCodesWithAdj)->unique()->toArray();

            if ($this->otFilter === 'with_ot') {
                $query->whereIn('employee_code', $mergedOtCodes);
            } elseif ($this->otFilter === 'without_ot') {
                $query->whereNotIn('employee_code', $mergedOtCodes);
            }
        }

        $total = (clone $query)->count();
        $totalPages = max(1, (int) ceil($total / $this->perPage));
        if ($this->page > $totalPages) {
            $this->page = $totalPages;
        }

        $items = $query
            ->forPage($this->page, $this->perPage)
            ->get();

        return [
            'items' => $items,
            'total' => $total,
            'totalPages' => $totalPages,
        ];
    }

    public function getEntriesMap(array $employeeCodes): array
    {
        if (empty($employeeCodes)) {
            return [];
        }

        $entries = OvertimeEntry::with(['block:id,name', 'creator:id,name'])
            ->whereMonth('ot_date', $this->record->month)
            ->whereYear('ot_date', $this->record->year)
            ->whereIn('employee_id', $employeeCodes)
            ->get();

        $map = [];
        foreach ($entries as $entry) {
            $day = $entry->ot_date->day;
            $map[$entry->employee_id][$day][] = [
                'id' => $entry->id,
                'block' => $entry->block?->name ?? '?',
                'hours' => (float) $entry->hours,
                'is_special_ot' => (bool) $entry->is_special_ot,
                'created_by_name' => $entry->creator?->name ?? 'System',
                'created_at' => $entry->created_at?->format('d M, Y h:i A') ?? '',
            ];
        }

        return $map;
    }

    public function getAdjustmentsMap(array $employeeCodes): array
    {
        if (empty($employeeCodes)) {
            return [];
        }

        $adjustments = OvertimeAdjustment::with(['block:id,name', 'creator:id,name'])
            ->where('overtime_report_id', $this->record->id)
            ->whereIn('employee_id', $employeeCodes)
            ->orderBy('adjustment_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $map = [];
        foreach ($adjustments as $adj) {
            $emp = $adj->employee_id;
            if (! isset($map[$emp])) {
                $map[$emp] = [
                    'normal' => 0.0,
                    'special' => 0.0,
                    'normal_entries' => [],
                    'special_entries' => [],
                ];
            }

            $type = $adj->ot_type === 'special' ? 'special' : 'normal';
            $hours = (float) $adj->hours;
            $map[$emp][$type] += $hours;
            $map[$emp]["{$type}_entries"][] = [
                'id' => $adj->id,
                'date' => $adj->adjustment_date?->format('d M, Y') ?? '—',
                'block' => $adj->block?->name,
                'hours' => $hours,
                'remarks' => (string) ($adj->remarks ?? ''),
                'created_by_name' => $adj->creator?->name ?? 'System',
                'created_at' => $adj->created_at?->format('d M, Y h:i A') ?? '',
            ];
        }

        return $map;
    }

    public function openCell(string $employeeCode, string $employeeName, int $day): void
    {
        $this->editingEmployeeCode = $employeeCode;
        $this->editingEmployeeName = $employeeName;
        $this->editingDay = $day;
        $this->newBlockId = $this->getBlocks()->first()?->id;
        $this->newHours = '';
    }

    public function closeCell(): void
    {
        $this->editingEmployeeCode = null;
        $this->editingEmployeeName = null;
        $this->editingDay = null;
        $this->newHours = '';
    }

    public function addCellEntry(): void
    {
        if (! $this->canEnterOtValues() || ! $this->editingEmployeeCode || ! $this->editingDay || ! $this->newBlockId) {
            return;
        }

        $hours = (float) $this->newHours;
        if ($hours <= 0 || $hours > 24) {
            return;
        }

        $date = Carbon::create($this->record->year, $this->record->month, $this->editingDay)->toDateString();

        OvertimeEntry::create([
            'employee_id' => $this->editingEmployeeCode,
            'ot_date' => $date,
            'ot_block_id' => $this->newBlockId,
            'hours' => $hours,
        ]);

        $this->closeCell();
    }

    public function deleteCellEntry(int $entryId): void
    {
        if (! $this->canEnterOtValues()) {
            return;
        }

        OvertimeEntry::where('id', $entryId)->delete();
    }

    public function openAdjustment(string $employeeCode, string $employeeName, string $type): void
    {
        $this->adjEmployeeCode = $employeeCode;
        $this->adjEmployeeName = $employeeName;
        $this->adjType = $type === 'special' ? 'special' : 'normal';
        $this->adjDate = now()->toDateString();
        $this->adjBlockId = null;
        $this->adjHours = '';
        $this->adjRemarks = '';
    }

    public function closeAdjustment(): void
    {
        $this->adjEmployeeCode = null;
        $this->adjEmployeeName = null;
        $this->adjType = null;
        $this->adjDate = '';
        $this->adjBlockId = null;
        $this->adjHours = '';
        $this->adjRemarks = '';
    }

    public function addAdjustmentEntry(): void
    {
        if (! $this->canEnterOtValues() || ! $this->adjEmployeeCode || ! $this->adjType) {
            return;
        }

        $hours = (float) $this->adjHours;
        if ($hours == 0.0 || abs($hours) > 500) {
            return;
        }

        OvertimeAdjustment::create([
            'overtime_report_id' => $this->record->id,
            'employee_id' => $this->adjEmployeeCode,
            'ot_type' => $this->adjType === 'special' ? 'special' : 'normal',
            'adjustment_date' => filled($this->adjDate) ? $this->adjDate : null,
            'ot_block_id' => $this->adjBlockId ?: null,
            'hours' => $hours,
            'remarks' => trim($this->adjRemarks) ?: null,
        ]);

        $this->adjHours = '';
        $this->adjRemarks = '';
    }

    public function deleteAdjustmentEntry(int $adjustmentId): void
    {
        if (! $this->canEnterOtValues()) {
            return;
        }

        OvertimeAdjustment::where('id', $adjustmentId)
            ->where('overtime_report_id', $this->record->id)
            ->delete();
    }
}
