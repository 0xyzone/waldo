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

    public int $page = 1;

    public int $perPage = 50;

    public ?string $editingEmployeeCode = null;

    public ?string $editingEmployeeName = null;

    public ?int $editingDay = null;

    public ?int $newBlockId = null;

    public string $newHours = '';

    public ?string $adjEmployeeCode = null;

    public ?string $adjEmployeeName = null;

    public string $adjNormal = '0';

    public string $adjSpecial = '0';

    public string $adjRemarks = '';

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
                ->pluck('employee_id')
                ->toArray();

            $query->whereIn('employee_code', $empCodesWithBlock);
        }

        if ($this->otFilter !== 'all') {
            $empCodesWithOt = OvertimeEntry::whereMonth('ot_date', $this->record->month)
                ->whereYear('ot_date', $this->record->year)
                ->distinct()
                ->pluck('employee_id')
                ->toArray();

            if ($this->otFilter === 'with_ot') {
                $query->whereIn('employee_code', $empCodesWithOt);
            } elseif ($this->otFilter === 'without_ot') {
                $query->whereNotIn('employee_code', $empCodesWithOt);
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

        return OvertimeAdjustment::where('overtime_report_id', $this->record->id)
            ->whereIn('employee_id', $employeeCodes)
            ->get()
            ->mapWithKeys(fn (OvertimeAdjustment $adj) => [
                $adj->employee_id => [
                    'normal' => (float) $adj->normal_adjustment,
                    'special' => (float) $adj->special_adjustment,
                    'remarks' => (string) ($adj->remarks ?? ''),
                ],
            ])
            ->toArray();
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
        if (! $this->editingEmployeeCode || ! $this->editingDay || ! $this->newBlockId) {
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
        OvertimeEntry::where('id', $entryId)->delete();
    }

    public function openAdjustment(string $employeeCode, string $employeeName): void
    {
        $existing = OvertimeAdjustment::where('overtime_report_id', $this->record->id)
            ->where('employee_id', $employeeCode)
            ->first();

        $this->adjEmployeeCode = $employeeCode;
        $this->adjEmployeeName = $employeeName;
        $this->adjNormal = (string) ($existing?->normal_adjustment ?? '0');
        $this->adjSpecial = (string) ($existing?->special_adjustment ?? '0');
        $this->adjRemarks = (string) ($existing?->remarks ?? '');
    }

    public function closeAdjustment(): void
    {
        $this->adjEmployeeCode = null;
        $this->adjEmployeeName = null;
        $this->adjNormal = '0';
        $this->adjSpecial = '0';
        $this->adjRemarks = '';
    }

    public function saveAdjustment(): void
    {
        if (! $this->adjEmployeeCode) {
            return;
        }

        $normal = (float) $this->adjNormal;
        $special = (float) $this->adjSpecial;

        OvertimeAdjustment::updateOrCreate(
            [
                'overtime_report_id' => $this->record->id,
                'employee_id' => $this->adjEmployeeCode,
            ],
            [
                'normal_adjustment' => $normal,
                'special_adjustment' => $special,
                'remarks' => trim($this->adjRemarks) ?: null,
            ]
        );

        $this->closeAdjustment();
    }
}
