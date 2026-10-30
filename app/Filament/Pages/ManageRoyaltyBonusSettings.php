<?php

namespace App\Filament\Pages;

use Agroezinger\FilamentShieldEnhanced\Traits\HasPageShield;
use App\Models\AppSetting;
use App\Models\Department;
use App\Models\Designation;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use UnitEnum;

class ManageRoyaltyBonusSettings extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::AdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = 'Royalty Bonus Management';

    protected static ?string $navigationLabel = 'Royalty Settings';

    protected static ?string $title = 'Royalty Bonus Settings';

    protected static ?string $slug = 'royalty-bonus/settings';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.manage-royalty-bonus-settings';

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }

    #[Url(as: 'view')]
    public string $viewMode = 'split'; // 'split', 'designations', 'rules'

    /**
     * Form state properties.
     */
    public ?string $royalty_absent_mode = 'boolean';

    public ?string $royalty_absent_amount = '500.00';

    public ?string $royalty_late_early_mode = 'countable';

    public ?string $royalty_late_early_threshold = '3';

    public ?string $royalty_late_early_penalty = '100.00';

    public ?string $royalty_missing_thumb_mode = 'countable';

    public ?string $royalty_missing_thumb_threshold = '2';

    public ?string $royalty_missing_thumb_penalty = '100.00';

    public ?string $royalty_suspension_amount = '1000.00';

    public bool $royalty_enforce_min_floor = true;

    /**
     * Designation management interactive properties.
     */
    #[Url(as: 'department')]
    public string $selectedDepartment = 'all';

    #[Url(as: 'search')]
    public string $searchDesignation = '';

    #[Url(as: 'filter')]
    public string $royaltyFilter = 'all'; // 'all', 'eligible', 'ineligible'

    public static function getShieldPagePermissions(): array
    {
        return [
            'view' => 'View Royalty Settings',
            'save' => 'Save Royalty Settings',
        ];
    }

    public function mount(): void
    {
        $this->royalty_absent_mode = AppSetting::get('royalty_absent_mode', 'boolean');
        $this->royalty_absent_amount = AppSetting::get('royalty_absent_amount', '500.00');
        $this->royalty_late_early_mode = AppSetting::get('royalty_late_early_mode', 'countable');
        $this->royalty_late_early_threshold = AppSetting::get('royalty_late_early_threshold', '3');
        $this->royalty_late_early_penalty = AppSetting::get('royalty_late_early_penalty', '100.00');
        $this->royalty_missing_thumb_mode = AppSetting::get('royalty_missing_thumb_mode', 'countable');
        $this->royalty_missing_thumb_threshold = AppSetting::get('royalty_missing_thumb_threshold', '2');
        $this->royalty_missing_thumb_penalty = AppSetting::get('royalty_missing_thumb_penalty', '100.00');
        $this->royalty_suspension_amount = AppSetting::get('royalty_suspension_amount', '1000.00');
        $this->royalty_enforce_min_floor = (bool) AppSetting::get('royalty_enforce_min_floor', '1');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Absent Days Deduction Rules')
                    ->description('Specify how absences impact the monthly royalty bonus.')
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('royalty_absent_mode')
                                ->label('Absent Deduction Mode')
                                ->options([
                                    'boolean' => 'Flat Deduction (Deduct once if absent days > 0)',
                                    'countable' => 'Per Absent Day (Multiply penalty by number of absent days)',
                                ])
                                ->required()
                                ->native(false),
                            TextInput::make('royalty_absent_amount')
                                ->label('Absent Deduction Amount (Rs.)')
                                ->numeric()
                                ->prefix('Rs.')
                                ->required()
                                ->helperText('Flat amount deducted if absent, or per absent day.'),
                        ]),
                    ]),

                Section::make('Late In & Early Out Deduction Rules')
                    ->description('Set tolerance grace thresholds and penalties for tardiness.')
                    ->icon(Heroicon::OutlinedClock)
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('royalty_late_early_mode')
                                ->label('Deduction Mode')
                                ->options([
                                    'countable' => 'Per Excess Incident (Multiply penalty by occurrences beyond threshold)',
                                    'boolean' => 'Flat Penalty (Deduct once if count exceeds threshold)',
                                ])
                                ->required()
                                ->native(false),
                            TextInput::make('royalty_late_early_threshold')
                                ->label('Grace Threshold (Count)')
                                ->numeric()
                                ->default(3)
                                ->required()
                                ->helperText('Occurrences up to this number incur no deduction.'),
                            TextInput::make('royalty_late_early_penalty')
                                ->label('Penalty Amount (Rs.)')
                                ->numeric()
                                ->prefix('Rs.')
                                ->required()
                                ->helperText('Amount per excess occurrence or flat penalty.'),
                        ]),
                    ]),

                Section::make('Missing Thumb / Biometric Punch Rules')
                    ->description('Configure penalties for unpunched / missing biometric entries.')
                    ->icon(Heroicon::OutlinedFingerPrint)
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('royalty_missing_thumb_mode')
                                ->label('Deduction Mode')
                                ->options([
                                    'countable' => 'Per Excess Incident (Multiply penalty by occurrences beyond threshold)',
                                    'boolean' => 'Flat Penalty (Deduct once if count exceeds threshold)',
                                ])
                                ->required()
                                ->native(false),
                            TextInput::make('royalty_missing_thumb_threshold')
                                ->label('Grace Threshold (Count)')
                                ->numeric()
                                ->default(2)
                                ->required()
                                ->helperText('Occurrences up to this number incur no deduction.'),
                            TextInput::make('royalty_missing_thumb_penalty')
                                ->label('Penalty Amount (Rs.)')
                                ->numeric()
                                ->prefix('Rs.')
                                ->required()
                                ->helperText('Amount per excess occurrence or flat penalty.'),
                        ]),
                    ]),

                Section::make('Suspension Deduction Rules')
                    ->description('Specify a fixed deduction amount when an employee has a suspension starting in the billing month.')
                    ->icon(Heroicon::OutlinedExclamationTriangle)
                    ->schema([
                        TextInput::make('royalty_suspension_amount')
                            ->label('Suspension Deduction Amount (Rs.)')
                            ->numeric()
                            ->prefix('Rs.')
                            ->required()
                            ->helperText('Fixed amount deducted once if the employee has any suspension record whose start_date falls in the report billing month.'),
                    ]),

                Section::make('Payout Protection & Floor')
                    ->description('Guarantee a minimum payout baseline for employees with bonuses.')
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->schema([
                        Toggle::make('royalty_enforce_min_floor')
                            ->label('Enforce Designation Monthly Minimum Payout Floor')
                            ->helperText('When enabled, the final monthly payout after attendance and performance deductions will never be lower than the designation\'s Monthly Minimum Payout.')
                            ->default(true),
                    ]),
            ]);
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    public function applyPreset(string $preset): void
    {
        match ($preset) {
            'standard' => [
                $this->royalty_absent_mode = 'boolean',
                $this->royalty_absent_amount = '500.00',
                $this->royalty_late_early_mode = 'countable',
                $this->royalty_late_early_threshold = '3',
                $this->royalty_late_early_penalty = '100.00',
                $this->royalty_missing_thumb_mode = 'countable',
                $this->royalty_missing_thumb_threshold = '2',
                $this->royalty_missing_thumb_penalty = '100.00',
                $this->royalty_suspension_amount = '1000.00',
                $this->royalty_enforce_min_floor = true,
            ],
            'strict' => [
                $this->royalty_absent_mode = 'countable',
                $this->royalty_absent_amount = '750.00',
                $this->royalty_late_early_mode = 'countable',
                $this->royalty_late_early_threshold = '2',
                $this->royalty_late_early_penalty = '150.00',
                $this->royalty_missing_thumb_mode = 'countable',
                $this->royalty_missing_thumb_threshold = '1',
                $this->royalty_missing_thumb_penalty = '150.00',
                $this->royalty_suspension_amount = '1500.00',
                $this->royalty_enforce_min_floor = true,
            ],
            'lenient' => [
                $this->royalty_absent_mode = 'boolean',
                $this->royalty_absent_amount = '300.00',
                $this->royalty_late_early_mode = 'boolean',
                $this->royalty_late_early_threshold = '4',
                $this->royalty_late_early_penalty = '200.00',
                $this->royalty_missing_thumb_mode = 'boolean',
                $this->royalty_missing_thumb_threshold = '3',
                $this->royalty_missing_thumb_penalty = '200.00',
                $this->royalty_suspension_amount = '500.00',
                $this->royalty_enforce_min_floor = true,
            ],
            default => null,
        };

        Notification::make()
            ->title('Policy Preset Applied: '.ucfirst($preset))
            ->body('Rule parameters updated. Click "Save Deduction Rules" to commit changes.')
            ->info()
            ->send();
    }

    public function save(): void
    {
        AppSetting::set('royalty_absent_mode', (string) ($this->royalty_absent_mode ?? 'boolean'));
        AppSetting::set('royalty_absent_amount', (string) ($this->royalty_absent_amount ?? '500.00'));
        AppSetting::set('royalty_late_early_mode', (string) ($this->royalty_late_early_mode ?? 'countable'));
        AppSetting::set('royalty_late_early_threshold', (string) ($this->royalty_late_early_threshold ?? '3'));
        AppSetting::set('royalty_late_early_penalty', (string) ($this->royalty_late_early_penalty ?? '100.00'));
        AppSetting::set('royalty_missing_thumb_mode', (string) ($this->royalty_missing_thumb_mode ?? 'countable'));
        AppSetting::set('royalty_missing_thumb_threshold', (string) ($this->royalty_missing_thumb_threshold ?? '2'));
        AppSetting::set('royalty_missing_thumb_penalty', (string) ($this->royalty_missing_thumb_penalty ?? '100.00'));
        AppSetting::set('royalty_suspension_amount', (string) ($this->royalty_suspension_amount ?? '1000.00'));
        AppSetting::set('royalty_enforce_min_floor', $this->royalty_enforce_min_floor ? '1' : '0');

        Notification::make()
            ->title('Settings Saved Successfully')
            ->body('Royalty bonus calculation and deduction rules have been updated.')
            ->success()
            ->send();
    }

    public function getDepartmentsProperty(): Collection
    {
        return Department::where('is_active', true)
            ->orderBy('rank')
            ->orderBy('name')
            ->get();
    }

    protected function getBaseScopedQuery(): Builder
    {
        $query = Designation::query()
            ->with('department')
            ->orderBy('department_id')
            ->orderBy('name');

        if ($this->selectedDepartment !== 'all') {
            $query->where('department_id', $this->selectedDepartment);
        }

        if (trim($this->searchDesignation) !== '') {
            $term = trim($this->searchDesignation);
            $query->where('name', 'like', "%{$term}%");
        }

        return $query;
    }

    public function getFilteredDesignationsProperty(): Collection
    {
        $query = $this->getBaseScopedQuery();

        if ($this->royaltyFilter === 'eligible') {
            $query->where('is_royalty_eligible', true);
        } elseif ($this->royaltyFilter === 'ineligible') {
            $query->where('is_royalty_eligible', false);
        }

        return $query->get();
    }

    public function getScopedAllCountProperty(): int
    {
        return (clone $this->getBaseScopedQuery())->count();
    }

    public function getScopedEligibleCountProperty(): int
    {
        return (clone $this->getBaseScopedQuery())->where('is_royalty_eligible', true)->count();
    }

    public function getScopedExcludedCountProperty(): int
    {
        return (clone $this->getBaseScopedQuery())->where('is_royalty_eligible', false)->count();
    }

    public function getTotalDesignationsCountProperty(): int
    {
        return Designation::count();
    }

    public function getEligibleDesignationsCountProperty(): int
    {
        return Designation::where('is_royalty_eligible', true)->count();
    }

    public function getExcludedDesignationsCountProperty(): int
    {
        return Designation::where('is_royalty_eligible', false)->count();
    }

    public function getEligiblePercentageProperty(): int
    {
        $total = $this->totalDesignationsCount;

        return $total > 0 ? (int) round(($this->eligibleDesignationsCount / $total) * 100) : 0;
    }

    public function getExcludedPercentageProperty(): int
    {
        $total = $this->totalDesignationsCount;

        return $total > 0 ? (int) round(($this->excludedDesignationsCount / $total) * 100) : 0;
    }

    public function getTotalMonthlyMaxBudgetProperty(): float
    {
        return (float) Designation::where('is_royalty_eligible', true)->sum('royalty_monthly_max');
    }

    public function toggleFloorProtection(): void
    {
        $this->royalty_enforce_min_floor = ! $this->royalty_enforce_min_floor;
    }

    public function toggleDesignation(int $id): void
    {
        $designation = Designation::find($id);
        if ($designation) {
            $designation->is_royalty_eligible = ! $designation->is_royalty_eligible;
            $designation->save();

            $status = $designation->is_royalty_eligible ? 'included in' : 'excluded from';
            Notification::make()
                ->title('Designation Eligibility Updated')
                ->body("{$designation->name} is now {$status} Royalty Bonus calculations.")
                ->success()
                ->send();
        }
    }

    public function updateDesignationBudget(int $id, mixed $amount): void
    {
        $designation = Designation::find($id);
        if (! $designation) {
            return;
        }

        $cleanAmount = is_numeric($amount) ? max(0.0, (float) $amount) : 0.0;
        $designation->royalty_annual_budget = $cleanAmount;
        $designation->save();

        Notification::make()
            ->title('Annual Budget Updated')
            ->body("{$designation->name}: Annual Rs. ".number_format($cleanAmount, 2).' → Monthly Max Rs. '.number_format((float) $designation->royalty_monthly_max, 2).' | Min Rs. '.number_format((float) $designation->royalty_monthly_min, 2))
            ->success()
            ->send();
    }

    public function setDepartment(string $deptId): void
    {
        $this->selectedDepartment = $deptId;
    }

    public function setRoyaltyFilter(string $filter): void
    {
        $this->royaltyFilter = $filter;
    }

    public function bulkSetEligibility(bool $status): void
    {
        $ids = $this->filteredDesignations->pluck('id');
        if ($ids->isNotEmpty()) {
            Designation::whereIn('id', $ids)->update(['is_royalty_eligible' => $status]);
            $action = $status ? 'included in' : 'excluded from';
            Notification::make()
                ->title('Bulk Update Complete')
                ->body(count($ids).' designation(s) have been '.$action.' Royalty Bonus calculations.')
                ->success()
                ->send();
        }
    }
}
