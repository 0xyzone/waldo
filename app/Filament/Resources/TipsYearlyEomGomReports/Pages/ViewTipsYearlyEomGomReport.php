<?php

namespace App\Filament\Resources\TipsYearlyEomGomReports\Pages;

use App\Filament\Resources\TipsYearlyEomGomReports\TipsYearlyEomGomReportResource;
use App\Models\Department;
use App\Models\Employee;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class ViewTipsYearlyEomGomReport extends ViewRecord
{
    protected static string $resource = TipsYearlyEomGomReportResource::class;

    protected string $view = 'filament.resources.tips-yearly-eom-gom-reports.pages.view-tips-yearly-eom-gom-report';

    public int $activeMonth = 1;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $currentMonth = (int) now()->format('n');
        $this->activeMonth = ($currentMonth >= 1 && $currentMonth <= 12) ? $currentMonth : 1;

        if (request()->query('month')) {
            $m = (int) request()->query('month');
            if ($m >= 1 && $m <= 12) {
                $this->activeMonth = $m;
            }
        }
    }

    public function setActiveMonth(int $month): void
    {
        if ($month >= 1 && $month <= 12) {
            $this->activeMonth = $month;
        }
    }

    public function getActivePeriodProperty(): array
    {
        return $this->record->getPeriodForMonth($this->activeMonth);
    }

    public function getActiveMonthNameProperty(): string
    {
        return $this->activePeriod['evaluated_month'];
    }

    public function getActiveYearProperty(): int
    {
        return $this->activePeriod['evaluated_year'];
    }

    public function getActivePeriodLabelProperty(): string
    {
        return $this->activePeriod['evaluated_label'];
    }

    public function getMonthEntriesProperty()
    {
        return $this->record->entries()
            ->where('month_number', $this->activeMonth)
            ->with(['department', 'eomEmployee1.designation', 'eomEmployee2.designation', 'gomEmployee1.designation'])
            ->orderBy('entry_number')
            ->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('printMonthly')
                ->label(fn () => "🖨️ Print {$this->activePeriodLabel} Sheet")
                ->color('gray')
                ->icon(Heroicon::OutlinedPrinter)
                ->url(fn () => route('tips.eom-gom.print-monthly', ['report' => $this->record->id, 'month' => $this->activeMonth]))
                ->openUrlInNewTab(),

            Action::make('hrmsCard')
                ->label(fn () => "🌟 {$this->activePeriodLabel} HRMS Wish Card")
                ->color('warning')
                ->icon(Heroicon::OutlinedSparkles)
                ->url(fn () => route('tips.eom-gom.hrms-card', ['report' => $this->record->id, 'month' => $this->activeMonth]))
                ->openUrlInNewTab(),

            Action::make('editMonthWinners')
                ->label(fn () => "✏️ Assign {$this->activePeriodLabel} Winners")
                ->color('primary')
                ->icon(Heroicon::OutlinedUserPlus)
                ->modalHeading(fn () => "Assign Winners for {$this->activePeriodLabel} ({$this->activePeriod['release_label']})")
                ->modalDescription('Assign Employees of the Month (EOM) and Groomings of the Month (GOM) for this month.')
                ->modalWidth('4xl')
                ->fillForm(function (): array {
                    $entries = $this->record->entries()->where('month_number', $this->activeMonth)->get();
                    $e1 = $entries->firstWhere('entry_number', 1);
                    $e2 = $entries->firstWhere('entry_number', 2);
                    $e3 = $entries->firstWhere('entry_number', 3);
                    $e4 = $entries->firstWhere('entry_number', 4);

                    return [
                        'e1_eom_1' => $e1?->eom_employee_code_1,
                        'e1_eom_rem_1' => $e1?->eom_remarks_1,
                        'e1_eom_2' => $e1?->eom_employee_code_2,
                        'e1_eom_rem_2' => $e1?->eom_remarks_2,
                        'e1_gom_1' => $e1?->gom_employee_code_1,
                        'e1_gom_rem_1' => $e1?->gom_remarks_1,

                        'e2_dept_id' => $e2?->department_id,
                        'e2_eom_1' => $e2?->eom_employee_code_1,
                        'e2_eom_rem_1' => $e2?->eom_remarks_1,

                        'e3_dept_id' => $e3?->department_id,
                        'e3_gom_1' => $e3?->gom_employee_code_1,
                        'e3_gom_rem_1' => $e3?->gom_remarks_1,

                        'e4_dept_id' => $e4?->department_id,
                        'e4_eom_1' => $e4?->eom_employee_code_1,
                        'e4_eom_rem_1' => $e4?->eom_remarks_1,
                    ];
                })
                ->form([
                    Section::make('Entry 1: Gaming / Slot Department (2 EOM, 1 GOM)')
                        ->description('Gaming & Slot department winners. Employees can be selected from Gaming or Slot.')
                        ->schema([
                            Grid::make(2)->schema([
                                Select::make('e1_eom_1')
                                    ->label('EOM 1 (Employee of the Month)')
                                    ->placeholder('Choose Gaming / Slot Employee')
                                    ->options(fn () => $this->getEmployeeOptions([6, 7]))
                                    ->searchable()
                                    ->preload()
                                    ->native(false),
                                TextInput::make('e1_eom_rem_1')
                                    ->label('Remarks / Recognition Note')
                                    ->placeholder('e.g. Exceptional customer satisfaction and teamwork'),

                                Select::make('e1_eom_2')
                                    ->label('EOM 2 (Employee of the Month)')
                                    ->placeholder('Choose Gaming / Slot Employee')
                                    ->options(fn () => $this->getEmployeeOptions([6, 7]))
                                    ->searchable()
                                    ->preload()
                                    ->native(false),
                                TextInput::make('e1_eom_rem_2')
                                    ->label('Remarks / Recognition Note')
                                    ->placeholder('e.g. Outstanding floor performance'),

                                Select::make('e1_gom_1')
                                    ->label('GOM 1 (Grooming of the Month)')
                                    ->placeholder('Choose Gaming / Slot Employee')
                                    ->options(fn () => $this->getEmployeeOptions([6, 7]))
                                    ->searchable()
                                    ->preload()
                                    ->native(false),
                                TextInput::make('e1_gom_rem_1')
                                    ->label('Remarks / Recognition Note')
                                    ->placeholder('e.g. Exemplary grooming, posture and uniform standards'),
                            ]),
                        ]),

                    Section::make('Entry 2: Allowed Department (1 EOM)')
                        ->description('Randomly selected department for Employee of the Month.')
                        ->schema([
                            Grid::make(2)->schema([
                                Select::make('e2_dept_id')
                                    ->label('Department')
                                    ->options(fn () => Department::whereNotIn('id', [6, 7])->orderBy('name')->pluck('name', 'id')->toArray())
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->live(),

                                Select::make('e2_eom_1')
                                    ->label('EOM (Employee of the Month)')
                                    ->placeholder('Select Department Employee')
                                    ->options(function ($get) {
                                        $deptId = $get('e2_dept_id');

                                        return $deptId ? $this->getEmployeeOptions([(int) $deptId]) : [];
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->native(false),

                                TextInput::make('e2_eom_rem_1')
                                    ->label('Remarks / Recognition Note')
                                    ->placeholder('e.g. Outstanding dedication & punctuality')
                                    ->columnSpanFull(),
                            ]),
                        ]),

                    Section::make('Entry 3: Allowed Department (1 GOM)')
                        ->description('Randomly selected department for Grooming of the Month.')
                        ->schema([
                            Grid::make(2)->schema([
                                Select::make('e3_dept_id')
                                    ->label('Department')
                                    ->options(fn () => Department::whereNotIn('id', [6, 7])->orderBy('name')->pluck('name', 'id')->toArray())
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->live(),

                                Select::make('e3_gom_1')
                                    ->label('GOM (Grooming of the Month)')
                                    ->placeholder('Select Department Employee')
                                    ->options(function ($get) {
                                        $deptId = $get('e3_dept_id');

                                        return $deptId ? $this->getEmployeeOptions([(int) $deptId]) : [];
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->native(false),

                                TextInput::make('e3_gom_rem_1')
                                    ->label('Remarks / Recognition Note')
                                    ->placeholder('e.g. Pristine presentation and corporate etiquette')
                                    ->columnSpanFull(),
                            ]),
                        ]),

                    Section::make('Entry 4: Allowed Department (1 EOM)')
                        ->description('Randomly selected department for Employee of the Month.')
                        ->schema([
                            Grid::make(2)->schema([
                                Select::make('e4_dept_id')
                                    ->label('Department')
                                    ->options(fn () => Department::whereNotIn('id', [6, 7])->orderBy('name')->pluck('name', 'id')->toArray())
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->live(),

                                Select::make('e4_eom_1')
                                    ->label('EOM (Employee of the Month)')
                                    ->placeholder('Select Department Employee')
                                    ->options(function ($get) {
                                        $deptId = $get('e4_dept_id');

                                        return $deptId ? $this->getEmployeeOptions([(int) $deptId]) : [];
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->native(false),

                                TextInput::make('e4_eom_rem_1')
                                    ->label('Remarks / Recognition Note')
                                    ->placeholder('e.g. Exceptional leadership & dedication')
                                    ->columnSpanFull(),
                            ]),
                        ]),
                ])
                ->action(function (array $data): void {
                    $entries = $this->record->entries()->where('month_number', $this->activeMonth)->get();
                    $e1 = $entries->firstWhere('entry_number', 1);
                    $e2 = $entries->firstWhere('entry_number', 2);
                    $e3 = $entries->firstWhere('entry_number', 3);
                    $e4 = $entries->firstWhere('entry_number', 4);

                    if ($e1) {
                        $e1->update([
                            'eom_employee_code_1' => $data['e1_eom_1'] ?: null,
                            'eom_remarks_1' => $data['e1_eom_rem_1'] ?: null,
                            'eom_employee_code_2' => $data['e1_eom_2'] ?: null,
                            'eom_remarks_2' => $data['e1_eom_rem_2'] ?: null,
                            'gom_employee_code_1' => $data['e1_gom_1'] ?: null,
                            'gom_remarks_1' => $data['e1_gom_rem_1'] ?: null,
                        ]);
                    }

                    if ($e2) {
                        $dept2 = ! empty($data['e2_dept_id']) ? Department::find($data['e2_dept_id']) : null;
                        $e2->update([
                            'department_id' => $dept2?->id ?? $e2->department_id,
                            'department_name' => $dept2?->name ?? $e2->department_name,
                            'eom_employee_code_1' => $data['e2_eom_1'] ?: null,
                            'eom_remarks_1' => $data['e2_eom_rem_1'] ?: null,
                        ]);
                    }

                    if ($e3) {
                        $dept3 = ! empty($data['e3_dept_id']) ? Department::find($data['e3_dept_id']) : null;
                        $e3->update([
                            'department_id' => $dept3?->id ?? $e3->department_id,
                            'department_name' => $dept3?->name ?? $e3->department_name,
                            'gom_employee_code_1' => $data['e3_gom_1'] ?: null,
                            'gom_remarks_1' => $data['e3_gom_rem_1'] ?: null,
                        ]);
                    }

                    if ($e4) {
                        $dept4 = ! empty($data['e4_dept_id']) ? Department::find($data['e4_dept_id']) : null;
                        $e4->update([
                            'department_id' => $dept4?->id ?? $e4->department_id,
                            'department_name' => $dept4?->name ?? $e4->department_name,
                            'eom_employee_code_1' => $data['e4_eom_1'] ?: null,
                            'eom_remarks_1' => $data['e4_eom_rem_1'] ?: null,
                        ]);
                    }

                    Notification::make()
                        ->title("{$this->activePeriodLabel} Winners Updated")
                        ->body("Successfully updated EOM & GOM winners for {$this->activePeriodLabel} ({$this->activePeriod['release_label']}).")
                        ->success()
                        ->send();
                }),

            Action::make('rerandomizeMonth')
                ->label('🎲 Re-randomize Departments')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading("Re-randomize {$this->activePeriodLabel} Allowed Departments?")
                ->modalDescription("This will re-select 3 random allowed departments for Entry 2, Entry 3, and Entry 4 for {$this->activePeriodLabel}. Existing employee assignments on those entries will be reset.")
                ->action(function (): void {
                    $this->record->rerandomizeMonth($this->activeMonth);

                    Notification::make()
                        ->title('Departments Re-randomized')
                        ->body("New random allowed departments selected for {$this->activePeriodLabel}.")
                        ->success()
                        ->send();
                }),

            EditAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getEmployeeOptions(array $departmentIds): array
    {
        return Employee::whereIn('department_id', $departmentIds)
            ->where('employee_status', 'Active')
            ->with('designation')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function ($emp) {
                $desig = $emp->designation?->name ? " ({$emp->designation->name})" : '';

                return [$emp->employee_code => "{$emp->employee_code} | {$emp->name}{$desig}"];
            })
            ->toArray();
    }
}
