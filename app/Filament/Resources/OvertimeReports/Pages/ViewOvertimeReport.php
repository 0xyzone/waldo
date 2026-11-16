<?php

namespace App\Filament\Resources\OvertimeReports\Pages;

use App\Filament\Resources\OvertimeReports\OvertimeReportResource;
use App\Models\Employee;
use App\Models\OtBlock;
use App\Models\OtSpecialDate;
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

    public int $page = 1;

    public int $perPage = 50;

    public ?string $editingEmployeeCode = null;

    public ?string $editingEmployeeName = null;

    public ?int $editingDay = null;

    public ?int $newBlockId = null;

    public string $newHours = '';

    public function updatedSearch(): void
    {
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

    public function getEmployeesData(): array
    {
        $query = Employee::with('department:id,name')
            ->select(['employee_code', 'name', 'department_id'])
            ->where('employee_status', 'active')
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

        $entries = OvertimeEntry::with('block:id,name')
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

        $this->newHours = '';
    }

    public function deleteCellEntry(int $entryId): void
    {
        OvertimeEntry::where('id', $entryId)->delete();
    }
}
