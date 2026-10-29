<?php

namespace App\Filament\Pages;

use Agroezinger\FilamentShieldEnhanced\Traits\HasPageShield;
use App\Models\Department;
use App\Models\RoyaltyReport;
use App\Services\RoyaltyCalculationService;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Url;
use UnitEnum;

class RoyaltyBalanceOverview extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::Scale;

    protected static string|UnitEnum|null $navigationGroup = 'Royalty Bonus Management';

    protected static ?string $navigationLabel = 'Balance Overview';

    protected static ?string $title = 'Royalty Budget Balance Overview';

    protected static ?string $slug = 'royalty-bonus/balance-overview';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.royalty-balance-overview';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'report')]
    public string $selectedReport = 'all';

    #[Url(as: 'department')]
    public string $selectedDepartment = 'all';

    #[Url(as: 'status')]
    public string $statusFilter = 'all';

    public ?string $viewingHistoryCode = null;

    public function mount(): void
    {
        if (request()->has('report') && request()->query('report') !== '') {
            $this->selectedReport = (string) request()->query('report');
        }
    }

    public function updatedSearch(): void
    {
        // livewire hook
    }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
    }

    public function openHistoryModal(string $code): void
    {
        $this->viewingHistoryCode = $code;
    }

    public function closeHistoryModal(): void
    {
        $this->viewingHistoryCode = null;
    }

    /**
     * @return Collection<int, RoyaltyReport>
     */
    public function getReportsProperty()
    {
        return RoyaltyReport::query()
            ->orderByDesc('year')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return Collection<int, Department>
     */
    public function getDepartmentsProperty()
    {
        return Department::query()->orderBy('name')->get();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecordsProperty(): array
    {
        $reportId = ($this->selectedReport !== 'all' && is_numeric($this->selectedReport))
            ? (int) $this->selectedReport
            : null;

        $dept = $this->selectedDepartment !== 'all' ? $this->selectedDepartment : null;

        $all = app(RoyaltyCalculationService::class)->getBalanceOverviewRecords($reportId, $dept);

        $search = strtolower(trim($this->search));
        $status = strtolower(trim($this->statusFilter));

        return array_values(array_filter($all, function ($r) use ($search, $status) {
            if ($search !== '') {
                $matchesCode = str_contains(strtolower($r['employee_code']), $search);
                $matchesName = str_contains(strtolower($r['employee_name']), $search);
                $matchesDesig = str_contains(strtolower($r['designation']), $search);
                $matchesDept = str_contains(strtolower($r['department']), $search);

                if (! ($matchesCode || $matchesName || $matchesDesig || $matchesDept)) {
                    return false;
                }
            }

            if ($status !== 'all') {
                $rStatus = strtolower($r['status']);
                if ($status === 'healthy' && $rStatus !== 'healthy') {
                    return false;
                }
                if ($status === 'low' && $rStatus !== 'low budget') {
                    return false;
                }
                if ($status === 'exhausted' && $rStatus !== 'exhausted') {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSelectedRecordProperty(): ?array
    {
        if (! $this->viewingHistoryCode) {
            return null;
        }

        foreach ($this->records as $r) {
            if ($r['employee_code'] === $this->viewingHistoryCode) {
                return $r;
            }
        }

        return null;
    }

    public function getTotalBudgetProperty(): float
    {
        return (float) array_sum(array_column($this->records, 'annual_budget'));
    }

    public function getTotalConsumedProperty(): float
    {
        return (float) array_sum(array_column($this->records, 'consumed_budget'));
    }

    public function getTotalRemainingProperty(): float
    {
        return (float) array_sum(array_column($this->records, 'remaining_budget'));
    }

    public function getAverageUtilizationProperty(): float
    {
        $total = $this->totalBudget;

        return $total > 0 ? round(($this->totalConsumed / $total) * 100, 1) : 0.0;
    }
}
