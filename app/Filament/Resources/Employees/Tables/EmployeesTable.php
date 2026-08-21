<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Models\Employee;
use App\Models\EmployeeSuspension;
use App\Models\TerminatedEmployee;
use App\Services\EmployeeExportService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    Split::make([
                        TextColumn::make('employee_code')
                            ->fontFamily('mono')
                            ->searchable()
                            ->sortable(query: function (Builder $query, string $direction): Builder {
                                $driver = $query->getConnection()->getDriverName();
                                if ($driver === 'sqlite') {
                                    return $query->orderByRaw('CAST(SUBSTR(employee_code, 4) AS INTEGER) '.$direction);
                                }

                                return $query->orderByRaw('CAST(SUBSTR(employee_code, 4) AS UNSIGNED) '.$direction);
                            })
                            ->color('gray')
                            ->grow(false),
                        Split::make([
                            TextColumn::make('employee_status')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'Active' => 'success',
                                    'Inactive' => 'gray',
                                    'Suspended' => 'warning',
                                    'Resigned' => 'danger',
                                    'Resigning this month', 'Resigning This Month' => 'warning',
                                    'Terminated' => 'danger',
                                    default => 'gray',
                                }),
                            TextColumn::make('onboarded')
                                ->formatStateUsing(fn (string $state) => $state === 'yes' ? '✅' : '❌'),
                            TextColumn::make('isIncomplete')
                                ->getStateUsing(function ($record) {
                                    return $record->isIncomplete() ? '⏳' : '☑️';
                                })
                                ->color(fn (string $state): string => match ($state) {
                                    '☑️' => 'success',
                                    '⏳' => 'gray',
                                })
                                ->tooltip(function ($record) {
                                    $fields = [
                                        'designation_id',
                                        'name',
                                        'gender',
                                        'join_date_formatted',
                                        'contact_number',
                                        'email',
                                        'citizenship_number',
                                        'citizenship_issue_date',
                                        'citizenship_issue_place',
                                        'dob_ad',
                                        'marital_status',
                                        'tips_amount',
                                        'point_value',
                                    ];

                                    $missing = collect($fields)->filter(fn ($field) => empty($record->$field));

                                    if ($missing->count() > 0) {
                                        return 'Missing fields: '.$missing->implode(', ');
                                    }

                                    return 'All fields complete';
                                }),
                        ])->grow(false),
                    ])->extraAttributes(['class' => 'justify-between items-center']),
                    TextColumn::make('name')
                        ->searchable()
                        ->sortable()
                        ->weight('bold')
                        ->size('lg')
                        ->extraAttributes(['class' => 'mt-3 block']),
                    TextColumn::make('department.name')
                        ->icon('heroicon-m-building-office-2')
                        ->iconColor('primary')
                        ->color('gray')
                        ->size('sm')
                        ->extraAttributes(['class' => 'mt-1 block']),
                    TextColumn::make('designation.name')
                        ->icon('heroicon-m-briefcase')
                        ->iconColor('primary')
                        ->color('gray')
                        ->size('sm')
                        ->extraAttributes(['class' => 'mt-1 block']),
                    TextColumn::make('dob_ad')
                        ->icon('heroicon-m-cake')
                        ->iconColor('primary')
                        ->color('gray')
                        ->date('jS F, Y')
                        ->size('sm')
                        ->extraAttributes(['class' => 'mt-1 block']),
                    TextColumn::make('join_date_formatted')
                        ->icon('heroicon-m-calendar')
                        ->color('gray')
                        ->size('sm')
                        ->extraAttributes(['class' => 'mt-3 block border-t pt-2 border-gray-150 dark:border-gray-800']),
                ]),
            ])
            ->contentGrid([
                'default' => 1,
                'sm' => 2,
                'md' => 3,
                'lg' => 4,
            ])
            ->recordClasses(fn (Employee $record) => match ($record->employee_status) {
                'Active' => null,
                'Inactive' => 'bg-gray-row border-gray-200 dark:border-gray-700',
                'Suspended' => 'bg-amber-row border-amber-200 dark:border-amber-900',
                'Resigned' => 'bg-rose-row border-rose-200 dark:border-rose-900',
                'Resigning This Month' => 'bg-violet-row border-violet-200 dark:border-violet-900',
                'Terminated' => 'bg-red-row border-red-200 dark:border-red-900',
                default => null,
            })
            ->paginationPageOptions([4 * 2, 4 * 4, 4 * 8, 4 * 10, 4 * 20])
            ->filters([
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('designation_id')
                    ->label('Designation')
                    ->relationship('designation', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('employee_status')
                    ->label('Status')
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
                        'Suspended' => 'Suspended',
                        'Resigned' => 'Resigned',
                        'Resigning this month' => 'Resigning this month',
                        'Terminated' => 'Terminated',
                    ])
                    ->native(false),
                Filter::make('join_date')
                    ->form([
                        DatePicker::make('join_date_from')
                            ->label('Joined From')
                            ->native(false),
                        DatePicker::make('join_date_to')
                            ->label('Joined To')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['join_date_from'],
                                function (Builder $query, string $date): Builder {
                                    $driver = $query->getConnection()->getDriverName();
                                    if ($driver === 'sqlite') {
                                        $codes = Employee::whereNotNull('join_date_formatted')
                                            ->get()
                                            ->filter(function ($emp) use ($date) {
                                                try {
                                                    return Carbon::createFromFormat('d F, Y', $emp->join_date_formatted)->format('Y-m-d') >= $date;
                                                } catch (\Throwable $e) {
                                                    return false;
                                                }
                                            })
                                            ->pluck('employee_code');

                                        return $query->whereIn('employee_code', $codes);
                                    }

                                    return $query->whereRaw("STR_TO_DATE(join_date_formatted, '%d %M, %Y') >= ?", [$date]);
                                }
                            )
                            ->when(
                                $data['join_date_to'],
                                function (Builder $query, string $date): Builder {
                                    $driver = $query->getConnection()->getDriverName();
                                    if ($driver === 'sqlite') {
                                        $codes = Employee::whereNotNull('join_date_formatted')
                                            ->get()
                                            ->filter(function ($emp) use ($date) {
                                                try {
                                                    return Carbon::createFromFormat('d F, Y', $emp->join_date_formatted)->format('Y-m-d') <= $date;
                                                } catch (\Throwable $e) {
                                                    return false;
                                                }
                                            })
                                            ->pluck('employee_code');

                                        return $query->whereIn('employee_code', $codes);
                                    }

                                    return $query->whereRaw("STR_TO_DATE(join_date_formatted, '%d %M, %Y') <= ?", [$date]);
                                }
                            );
                    }),
                SelectFilter::make('gender')
                    ->label('Gender')
                    ->options([
                        'Male' => 'Male',
                        'Female' => 'Female',
                        'Other' => 'Other',
                    ])
                    ->native(false),
                SelectFilter::make('marital_status')
                    ->label('Marital Status')
                    ->options([
                        'Married' => 'Married',
                        'Single' => 'Single',
                        'Others' => 'Others',
                    ])
                    ->native(false),
                TernaryFilter::make('incomplete')
                    ->label('Profile Completion')
                    ->placeholder('All Employees')
                    ->trueLabel('Incomplete Profiles Only')
                    ->falseLabel('Complete Profiles Only')
                    ->queries(
                        true: fn (Builder $query) => $query->isIncomplete(),
                        false: fn (Builder $query) => $query->where(function (Builder $q) {
                            $q
                                ->whereNotNull('designation_id')
                                ->whereNotNull('name')
                                ->whereNotNull('gender')
                                ->whereNotNull('join_date_formatted')
                                ->whereNotNull('contact_number')
                                ->whereNotNull('email')
                                ->whereNotNull('citizenship_number')
                                ->whereNotNull('citizenship_issue_date')
                                ->whereNotNull('citizenship_issue_place')
                                ->whereNotNull('dob_ad')
                                ->whereNotNull('marital_status')
                                ->whereNotNull('tips_amount')
                                ->whereNotNull('point_value');
                        }),
                    ),
            ])
            ->filtersFormColumns(3)
            ->defaultSort('employee_code', 'desc')
            ->recordActions([
                ViewAction::make()->modalWidth('7xl'),
                EditAction::make()->modalWidth('7xl'),
                ActionGroup::make([
                    Action::make('suspend')
                        ->label('Suspend')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Suspend Employee')
                        ->modalDescription(fn (Employee $record) => "Are you sure you want to suspend {$record->name} ({$record->employee_code})? This will update their status to Suspended and tips status to Hold.")
                        ->modalSubmitActionLabel('Confirm Suspension')
                        ->form([
                            DatePicker::make('start_date')
                                ->label('Suspended From')
                                ->native(false)
                                ->default(now())
                                ->required(),
                            DatePicker::make('end_date')
                                ->label('Suspended To')
                                ->native(false)
                                ->default(now()->addDays(7))
                                ->afterOrEqual('start_date')
                                ->required(),
                            Textarea::make('reason')
                                ->label('Reason for Suspension')
                                ->placeholder('Describe the incident, breach of policy, or reason for suspension...')
                                ->rows(3)
                                ->required(),
                            FileUpload::make('attachments')
                                ->label('Attachments / Evidence')
                                ->multiple()
                                ->directory('suspension-attachments')
                                ->disk('public')
                                ->maxSize(10240),
                        ])
                        ->visible(fn (Employee $record): bool => Auth::user()->hasRole(['super_admin', 'HR']) && $record->employee_status !== 'Suspended')
                        ->action(function (Employee $record, array $data): void {
                            EmployeeSuspension::create([
                                'employee_id' => $record->employee_code,
                                'start_date' => $data['start_date'],
                                'end_date' => $data['end_date'],
                                'reason' => $data['reason'],
                                'attachments' => $data['attachments'] ?? null,
                                'status' => 'active',
                            ]);

                            Notification::make()
                                ->title('Employee Suspended')
                                ->body("{$record->name} ({$record->employee_code}) has been marked as Suspended and Tips set to Hold.")
                                ->warning()
                                ->send();
                        }),
                    Action::make('terminate')
                        ->label('Terminate')
                        ->icon('heroicon-o-user-minus')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Terminate Employee')
                        ->modalDescription(fn (Employee $record) => "Are you sure you want to terminate {$record->name} ({$record->employee_code})? This will set their status to Terminated.")
                        ->modalSubmitActionLabel('Confirm Termination')
                        ->form([
                            DatePicker::make('last_working_date')
                                ->label('Last Date of Working')
                                ->native(false)
                                ->default(now())
                                ->required(),
                            DatePicker::make('termination_date')
                                ->label('Date of Termination')
                                ->native(false)
                                ->default(now())
                                ->required(),
                            Textarea::make('reason')
                                ->label('Reason for Termination')
                                ->rows(3),
                        ])
                        ->visible(fn (): bool => Auth::user()->hasRole(['super_admin', 'HR']))
                        ->action(function (Employee $record, array $data): void {
                            TerminatedEmployee::create([
                                'employee_id' => $record->employee_code,
                                'last_working_date' => $data['last_working_date'],
                                'termination_date' => $data['termination_date'],
                                'reason' => $data['reason'] ?? null,
                            ]);

                            Notification::make()
                                ->title('Employee Terminated')
                                ->body("{$record->name} ({$record->employee_code}) has been marked as Terminated.")
                                ->success()
                                ->send();
                        }),
                    Action::make('onboarded')
                        ->label('Onboard')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Onboard Employee')
                        ->modalDescription(fn (Employee $record) => "Are you sure you want to onboard {$record->name} ({$record->employee_code})?")
                        ->modalSubmitActionLabel('Confirm Onboarding')
                        ->visible(fn (Employee $record): bool => Auth::user()->hasRole(['super_admin', 'HR']) && $record->onboarded === 'no')
                        ->action(function (Employee $record): void {
                            $record->update(['onboarded' => 'yes']);

                            Notification::make()
                                ->title('Employee Onboarded')
                                ->body("{$record->name} ({$record->employee_code}) has been marked as Onboarded.")
                                ->success()
                                ->send();
                        }),
                ])
                    ->label('Acts')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
            ])
            ->toolbarActions([
                Action::make('export')
                    ->label('Export Data')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->modalHeading('Export Filtered Employees')
                    ->modalDescription('Choose file format and select header columns to include in your report.')
                    ->modalSubmitActionLabel('Download Report')
                    ->form([
                        Radio::make('format')
                            ->label('Export Format')
                            ->options([
                                'xlsx' => 'Excel Spreadsheet (.xlsx) — formatted with status colors',
                                'csv' => 'CSV File (.csv) — plain text data',
                            ])
                            ->default('xlsx')
                            ->required(),
                        Toggle::make('apply_styling')
                            ->label('Apply Employee Status Colors & Formatting (Excel only)')
                            ->default(true),
                        CheckboxList::make('columns')
                            ->label('Select Headers to Include')
                            ->options(EmployeeExportService::getAvailableColumns())
                            ->default(array_keys(EmployeeExportService::getAvailableColumns()))
                            ->columns(3)
                            ->required()
                            ->bulkToggleable(),
                    ])
                    ->action(function (array $data, HasTable $livewire, EmployeeExportService $service) {
                        $employees = $livewire
                            ->getFilteredTableQuery()
                            ->with(['department', 'designation'])
                            ->get();

                        return $service->export(
                            $employees,
                            $data['columns'] ?? array_keys(EmployeeExportService::getAvailableColumns()),
                            $data['format'] ?? 'xlsx',
                            (bool) ($data['apply_styling'] ?? true)
                        );
                    }),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('onboarded')
                        ->label('Onboard Selected')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Onboard Selected Employees')
                        ->modalDescription('Are you sure you want to mark all selected employees as Onboarded?')
                        ->modalSubmitActionLabel('Confirm Onboarding')
                        ->action(function (Collection $records): void {
                            $records->each(fn (Employee $record) => $record->update(['onboarded' => 'yes']));

                            Notification::make()
                                ->title('Employees Onboarded')
                                ->body("{$records->count()} employee(s) have been marked as Onboarded.")
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
