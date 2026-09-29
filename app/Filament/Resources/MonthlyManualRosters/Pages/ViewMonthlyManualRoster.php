<?php

namespace App\Filament\Resources\MonthlyManualRosters\Pages;

use App\Filament\Resources\MonthlyManualRosters\MonthlyManualRosterResource;
use App\Models\Employee;
use App\Models\ManualAttendanceEmployee;
use App\Models\MonthlyManualRosterItem;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class ViewMonthlyManualRoster extends ViewRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = MonthlyManualRosterResource::class;

    protected string $view = 'filament.resources.monthly-manual-rosters.pages.view-monthly-manual-roster';

    public string $activeDepartment = 'ALL';

    public string $activeStatus = 'ALL';

    public function setActiveDepartment(string $dept): void
    {
        $this->activeDepartment = $dept;
        $this->resetTable();
    }

    public function setActiveStatus(string $status): void
    {
        $this->activeStatus = $status;
        $this->resetTable();
    }

    public function getDepartmentsProperty(): array
    {
        $depts = MonthlyManualRosterItem::where('monthly_manual_roster_id', $this->record->id)
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department')
            ->toArray();

        return array_merge(['ALL'], $depts);
    }

    public function getDepartmentCount(string $dept): int
    {
        $query = MonthlyManualRosterItem::where('monthly_manual_roster_id', $this->record->id);

        if ($dept !== 'ALL') {
            $query->where('department', $dept);
        }

        if ($this->activeStatus === 'updated') {
            $query->where('is_roster_updated', true);
        } elseif ($this->activeStatus === 'pending') {
            $query->where('is_roster_updated', false);
        }

        return $query->count();
    }

    public function getTotalCountProperty(): int
    {
        return MonthlyManualRosterItem::where('monthly_manual_roster_id', $this->record->id)->count();
    }

    public function getUpdatedCountProperty(): int
    {
        return MonthlyManualRosterItem::where('monthly_manual_roster_id', $this->record->id)
            ->where('is_roster_updated', true)
            ->count();
    }

    public function getPendingCountProperty(): int
    {
        return MonthlyManualRosterItem::where('monthly_manual_roster_id', $this->record->id)
            ->where('is_roster_updated', false)
            ->count();
    }

    public function getCompletionPercentageProperty(): int
    {
        $total = $this->totalCount;
        if ($total <= 0) {
            return 0;
        }

        return (int) round(($this->updatedCount / $total) * 100);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $query = MonthlyManualRosterItem::query()
                    ->where('monthly_manual_roster_id', $this->record->id);

                if ($this->activeDepartment !== 'ALL') {
                    $query->where('department', $this->activeDepartment);
                }

                if ($this->activeStatus === 'updated') {
                    $query->where('is_roster_updated', true);
                } elseif ($this->activeStatus === 'pending') {
                    $query->where('is_roster_updated', false);
                }

                return $query
                    ->orderBy('department')
                    ->orderBy('designation')
                    ->orderByNumericCode();
            })
            ->columns([
                CheckboxColumn::make('is_roster_updated')
                    ->label('Roster Done')
                    ->afterStateUpdated(function (MonthlyManualRosterItem $record, bool $state): void {
                        $record->update([
                            'is_roster_updated' => $state,
                            'updated_at_hrms' => $state ? now() : null,
                            'updated_by' => $state ? Auth::id() : null,
                        ]);

                        $this->record->refreshStatistics();

                        Notification::make()
                            ->title($state ? 'Roster Marked as Done' : 'Roster Marked as Pending')
                            ->body("{$record->employee_name} ({$record->employee_code}) updated.")
                            ->success()
                            ->send();

                        $this->flushCachedTableRecords();
                    }),

                TextColumn::make('#')
                    ->rowIndex(),

                TextColumn::make('employee_code')
                    ->label('Code')
                    ->fontFamily('mono')
                    ->copyable()
                    ->copyMessage('Code copied')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employee_name')
                    ->label('Employee Name')
                    ->weight('semibold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('department')
                    ->label('Department')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('designation')
                    ->label('Designation')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('updated_at_hrms')
                    ->label('Updated At')
                    ->dateTime('d M, h:i A')
                    ->placeholder('Pending')
                    ->sortable(),

                TextColumn::make('updater.name')
                    ->label('Updated By')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->placeholder('—')
                    ->limit(30)
                    ->tooltip(fn (?string $state): ?string => $state),
            ])
            ->headerActions([
                Action::make('syncEmployees')
                    ->label('Sync Active Employees')
                    ->icon('heroicon-m-arrow-path')
                    ->color('primary')
                    ->tooltip('Import any newly added active manual attendance employees into this checklist')
                    ->action(function (): void {
                        $existingCodes = MonthlyManualRosterItem::where('monthly_manual_roster_id', $this->record->id)
                            ->pluck('employee_code')
                            ->toArray();

                        $missingEmployees = ManualAttendanceEmployee::active()
                            ->with(['employee.department', 'employee.designation'])
                            ->whereNotIn('employee_code', $existingCodes)
                            ->get();

                        if ($missingEmployees->isEmpty()) {
                            Notification::make()
                                ->title('Roster Up to Date')
                                ->body('All active manual attendance employees are already in this checklist.')
                                ->info()
                                ->send();

                            return;
                        }

                        $count = 0;
                        foreach ($missingEmployees as $manualEmp) {
                            $emp = $manualEmp->employee;
                            MonthlyManualRosterItem::create([
                                'monthly_manual_roster_id' => $this->record->id,
                                'employee_code' => $manualEmp->employee_code,
                                'employee_name' => $emp?->name ?: $manualEmp->employee_code,
                                'department' => $emp?->department?->name,
                                'designation' => $emp?->designation?->name,
                                'is_roster_updated' => false,
                            ]);
                            $count++;
                        }

                        $this->record->refreshStatistics();

                        Notification::make()
                            ->title('Employees Synced')
                            ->body("Added {$count} newly designated employee(s) to this monthly roster.")
                            ->success()
                            ->send();

                        $this->resetTable();
                    }),

                Action::make('addSingleEmployee')
                    ->label('Add Employee')
                    ->icon('heroicon-m-user-plus')
                    ->color('gray')
                    ->modalHeading('Add Employee to This Monthly Roster')
                    ->schema([
                        Select::make('employee_code')
                            ->label('Select Employee')
                            ->options(function (): array {
                                $existingCodes = MonthlyManualRosterItem::where('monthly_manual_roster_id', $this->record->id)
                                    ->pluck('employee_code')
                                    ->toArray();

                                return Employee::query()
                                    ->with(['department', 'designation'])
                                    ->whereNotIn('employee_code', $existingCodes)
                                    ->orderBy('employee_code')
                                    ->get()
                                    ->mapWithKeys(function (Employee $emp): array {
                                        $dept = $emp->department?->name ?? 'No Dept';
                                        $desig = $emp->designation?->name ? " • {$emp->designation->name}" : '';

                                        return [$emp->employee_code => "{$emp->employee_code} — {$emp->name} ({$dept}{$desig})"];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set): void {
                                if (! $state) {
                                    return;
                                }
                                $emp = Employee::with(['department', 'designation'])->find($state);
                                if ($emp) {
                                    $set('employee_name', $emp->name);
                                    $set('department', $emp->department?->name);
                                    $set('designation', $emp->designation?->name);
                                }
                            })
                            ->required(),

                        Grid::make(3)->schema([
                            TextInput::make('employee_name')
                                ->label('Name')
                                ->required(),

                            TextInput::make('department')
                                ->label('Department'),

                            TextInput::make('designation')
                                ->label('Designation'),
                        ]),

                        Textarea::make('notes')
                            ->label('Notes / Shift Details')
                            ->placeholder('e.g. Special shift schedule'),
                    ])
                    ->action(function (array $data): void {
                        MonthlyManualRosterItem::create([
                            'monthly_manual_roster_id' => $this->record->id,
                            'employee_code' => $data['employee_code'],
                            'employee_name' => $data['employee_name'],
                            'department' => $data['department'] ?? null,
                            'designation' => $data['designation'] ?? null,
                            'is_roster_updated' => false,
                            'notes' => $data['notes'] ?? null,
                        ]);

                        $this->record->refreshStatistics();

                        Notification::make()
                            ->title('Employee Added')
                            ->body("Added {$data['employee_name']} to this checklist.")
                            ->success()
                            ->send();

                        $this->resetTable();
                    }),

                Action::make('markAllUpdated')
                    ->label('Mark All Done')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Mark All as Done')
                    ->modalDescription('Are you sure you want to mark all manual attendance employees as roster updated in HRMS?')
                    ->action(function (): void {
                        MonthlyManualRosterItem::where('monthly_manual_roster_id', $this->record->id)
                            ->update([
                                'is_roster_updated' => true,
                                'updated_at_hrms' => now(),
                                'updated_by' => Auth::id(),
                            ]);

                        $this->record->refreshStatistics();

                        Notification::make()
                            ->title('All Marked as Done')
                            ->body('All employees marked as updated in HRMS.')
                            ->success()
                            ->send();

                        $this->resetTable();
                    }),
            ])
            ->actions([
                Action::make('editNotes')
                    ->label('Note')
                    ->icon('heroicon-m-pencil-square')
                    ->color('gray')
                    ->modalHeading(fn (MonthlyManualRosterItem $record): string => "Edit Notes: {$record->employee_name}")
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes / Remarks')
                            ->default(fn (MonthlyManualRosterItem $record): ?string => $record->notes)
                            ->rows(3),
                    ])
                    ->action(function (MonthlyManualRosterItem $record, array $data): void {
                        $record->update(['notes' => $data['notes'] ?? null]);
                        Notification::make()
                            ->title('Notes Saved')
                            ->success()
                            ->send();
                        $this->flushCachedTableRecords();
                    }),

                DeleteAction::make()
                    ->label('Remove')
                    ->modalHeading('Remove from this Checklist')
                    ->after(function (): void {
                        $this->record->refreshStatistics();
                        $this->flushCachedTableRecords();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('markSelectedDone')
                        ->label('Mark Selected Done')
                        ->icon('heroicon-m-check')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each(fn (MonthlyManualRosterItem $r) => $r->update([
                                'is_roster_updated' => true,
                                'updated_at_hrms' => now(),
                                'updated_by' => Auth::id(),
                            ]));

                            $this->record->refreshStatistics();

                            Notification::make()
                                ->title('Marked Done')
                                ->body("{$records->count()} employee(s) marked as roster updated.")
                                ->success()
                                ->send();

                            $this->flushCachedTableRecords();
                        }),

                    BulkAction::make('markSelectedPending')
                        ->label('Mark Selected Pending')
                        ->icon('heroicon-m-arrow-path')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each(fn (MonthlyManualRosterItem $r) => $r->update([
                                'is_roster_updated' => false,
                                'updated_at_hrms' => null,
                                'updated_by' => null,
                            ]));

                            $this->record->refreshStatistics();

                            Notification::make()
                                ->title('Marked Pending')
                                ->body("{$records->count()} employee(s) reset to pending.")
                                ->warning()
                                ->send();

                            $this->flushCachedTableRecords();
                        }),

                    DeleteBulkAction::make()
                        ->label('Remove Selected')
                        ->after(function (): void {
                            $this->record->refreshStatistics();
                            $this->flushCachedTableRecords();
                        }),
                ]),
            ])
            ->defaultSort('department');
    }
}
