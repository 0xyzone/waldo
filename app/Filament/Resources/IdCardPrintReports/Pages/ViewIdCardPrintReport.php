<?php

namespace App\Filament\Resources\IdCardPrintReports\Pages;

use App\Filament\Resources\IdCardPrintReports\IdCardPrintReportResource;
use App\Models\Employee;
use App\Models\IdCardPrintReportItem;
use App\Services\IdCardPrintProcessingService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ViewIdCardPrintReport extends ViewRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = IdCardPrintReportResource::class;

    protected string $view = 'filament.resources.id-card-print-reports.pages.view-id-card-print-report';

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
        $depts = IdCardPrintReportItem::where('id_card_print_report_id', $this->record->id)
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
        $query = IdCardPrintReportItem::where('id_card_print_report_id', $this->record->id);

        if ($dept !== 'ALL') {
            $query->where('department', $dept);
        }

        if ($this->activeStatus !== 'ALL') {
            $query->where('status', $this->activeStatus);
        }

        return $query->count();
    }

    public function getTotalCountProperty(): int
    {
        return IdCardPrintReportItem::where('id_card_print_report_id', $this->record->id)->count();
    }

    public function getSentForPrintCountProperty(): int
    {
        return IdCardPrintReportItem::where('id_card_print_report_id', $this->record->id)
            ->where('status', 'sent for print')
            ->count();
    }

    public function getPrintedCountProperty(): int
    {
        return IdCardPrintReportItem::where('id_card_print_report_id', $this->record->id)
            ->where('status', 'printed')
            ->count();
    }

    public function getInOfficeCountProperty(): int
    {
        return IdCardPrintReportItem::where('id_card_print_report_id', $this->record->id)
            ->where('status', 'in office')
            ->count();
    }

    public function getReleasedCountProperty(): int
    {
        return IdCardPrintReportItem::where('id_card_print_report_id', $this->record->id)
            ->where('status', 'released')
            ->count();
    }

    public function getOnHoldCountProperty(): int
    {
        return IdCardPrintReportItem::where('id_card_print_report_id', $this->record->id)
            ->where('status', 'on hold')
            ->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $query = IdCardPrintReportItem::query()
                    ->where('id_card_print_report_id', $this->record->id);

                if ($this->activeDepartment !== 'ALL') {
                    $query->where('department', $this->activeDepartment);
                }

                if ($this->activeStatus !== 'ALL') {
                    $query->where('status', $this->activeStatus);
                }

                return $query
                    ->orderBy('department')
                    ->orderBy('designation')
                    ->orderByNumericCode();
            })
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),

                TextColumn::make('employee_code')
                    ->label('Code')
                    ->copyable()
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderByNumericCode($direction))
                    ->weight('bold'),

                TextColumn::make('employee_name')
                    ->label('Employee Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('department')
                    ->label('Department')
                    ->badge()
                    ->color('gray')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('designation')
                    ->label('Designation')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('ID Card Status')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'sent for print' => 'gray',
                        'printed' => 'info',
                        'in office' => 'warning',
                        'released' => 'success',
                        'on hold' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Notes / Receiver')
                    ->placeholder('-')
                    ->limit(25),

                TextColumn::make('status_updated_at')
                    ->label('Last Status Change')
                    ->dateTime('d M, h:i A')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'sent for print' => 'Sent for Print',
                        'printed' => 'Printed',
                        'in office' => 'In Office',
                        'released' => 'Released',
                        'on hold' => 'On Hold',
                    ]),
            ])
            ->headerActions([
                Action::make('addEmployee')
                    ->label('Add Employee')
                    ->icon('heroicon-m-user-plus')
                    ->color('primary')
                    ->modalHeading('Add Employee to Batch')
                    ->schema([
                        Select::make('employee_code')
                            ->label('Select Existing Employee')
                            ->options(fn () => Employee::with(['department', 'designation'])
                                ->orderBy('employee_code')
                                ->get()
                                ->mapWithKeys(fn ($e) => [
                                    $e->employee_code => strtoupper($e->employee_code).' — '.$e->name.' ('.($e->department?->name ?? 'No Dept').')',
                                ]))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (! $state) {
                                    return;
                                }
                                $emp = Employee::with(['department', 'designation'])->where('employee_code', $state)->first();
                                if ($emp) {
                                    $name = $emp->name ?: trim(($emp->first_name ?? '').' '.($emp->last_name ?? ''));
                                    $set('employee_name', $name);
                                    $set('department', $emp->department?->name ?? '');
                                    $set('designation', $emp->designation?->name ?? '');
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

                        Select::make('status')
                            ->label('Card Status')
                            ->options([
                                'sent for print' => 'Sent for Print',
                                'printed' => 'Printed',
                                'in office' => 'In Office',
                                'released' => 'Released',
                                'on hold' => 'On Hold',
                            ])
                            ->default('sent for print')
                            ->required(),

                        TextInput::make('notes')
                            ->label('Notes / Receiver')
                            ->placeholder('Optional notes...'),
                    ])
                    ->action(function (array $data) {
                        IdCardPrintReportItem::create([
                            'id_card_print_report_id' => $this->record->id,
                            'employee_code' => $data['employee_code'],
                            'employee_name' => $data['employee_name'],
                            'department' => $data['department'] ?? null,
                            'designation' => $data['designation'] ?? null,
                            'status' => $data['status'] ?? 'sent for print',
                            'notes' => $data['notes'] ?? null,
                            'status_updated_at' => now(),
                        ]);

                        $this->record->increment('total_records');

                        Notification::make()
                            ->title('Employee Added')
                            ->body("Added {$data['employee_name']} to batch.")
                            ->success()
                            ->send();

                        $this->resetTable();
                    }),

                Action::make('printDepartmentSheet')
                    ->label(fn () => $this->activeDepartment === 'ALL' ? 'Print Sheet (All)' : 'Print '.$this->activeDepartment.' Sheet')
                    ->icon('heroicon-m-printer')
                    ->color('success')
                    ->url(fn () => route('id-card-print-reports.print', [
                        'report' => $this->record->id,
                        'department' => $this->activeDepartment !== 'ALL' ? $this->activeDepartment : null,
                        'status' => $this->activeStatus !== 'ALL' ? $this->activeStatus : null,
                    ]))
                    ->openUrlInNewTab(),
            ])
            ->actions([
                Action::make('markAsPrinted')
                    ->label('Mark as Printed')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->visible(fn (IdCardPrintReportItem $record): bool => strtolower($record->status) !== 'printed')
                    ->action(function (IdCardPrintReportItem $record) {
                        $record->update([
                            'status' => 'printed',
                            'status_updated_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Card Marked as Printed')
                            ->body("{$record->employee_name} ({$record->employee_code}) marked as Printed.")
                            ->success()
                            ->send();

                        $this->resetTable();
                    }),

                Action::make('updateStatus')
                    ->label('Status')
                    ->icon('heroicon-m-arrow-path')
                    ->color('primary')
                    ->modalHeading(fn ($record) => "Update Card Status: {$record->employee_name} ({$record->employee_code})")
                    ->schema([
                        Select::make('status')
                            ->label('New Status')
                            ->options([
                                'sent for print' => 'Sent for Print (In Production)',
                                'printed' => 'Printed (Received in Office)',
                                'in office' => 'In Office (Ready for Pickup)',
                                'released' => 'Released (Handed Over)',
                                'on hold' => 'On Hold',
                            ])
                            ->default(fn ($record) => $record->status)
                            ->required(),

                        TextInput::make('notes')
                            ->label('Notes / Handover Details')
                            ->placeholder('e.g. Handed to John, ID verified...')
                            ->default(fn ($record) => $record->notes),
                    ])
                    ->action(function (IdCardPrintReportItem $record, array $data) {
                        $record->update([
                            'status' => $data['status'],
                            'notes' => $data['notes'] ?? $record->notes,
                            'status_updated_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Card Status Updated')
                            ->body("Status updated to '{$data['status']}'.")
                            ->success()
                            ->send();

                        $this->resetTable();
                    }),

                Action::make('editItem')
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square')
                    ->color('gray')
                    ->schema([
                        TextInput::make('employee_name')->label('Name')->required(),
                        TextInput::make('department')->label('Department'),
                        TextInput::make('designation')->label('Designation'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'sent for print' => 'Sent for Print',
                                'printed' => 'Printed',
                                'in office' => 'In Office',
                                'released' => 'Released',
                                'on hold' => 'On Hold',
                            ])
                            ->required(),
                        TextInput::make('notes')->label('Notes'),
                    ])
                    ->fillForm(fn ($record) => [
                        'employee_name' => $record->employee_name,
                        'department' => $record->department,
                        'designation' => $record->designation,
                        'status' => $record->status,
                        'notes' => $record->notes,
                    ])
                    ->action(function (IdCardPrintReportItem $record, array $data) {
                        $record->update([
                            'employee_name' => $data['employee_name'],
                            'department' => $data['department'],
                            'designation' => $data['designation'],
                            'status' => $data['status'],
                            'notes' => $data['notes'],
                            'status_updated_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Item Updated')
                            ->success()
                            ->send();

                        $this->resetTable();
                    }),

                Action::make('deleteItem')
                    ->label('Remove')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (IdCardPrintReportItem $record) {
                        $record->delete();
                        $this->record->decrement('total_records');

                        Notification::make()
                            ->title('Item Removed')
                            ->success()
                            ->send();

                        $this->resetTable();
                    }),
            ])
            ->bulkActions([
                BulkAction::make('markAsPrintedBulk')
                    ->label('Mark as Printed')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Mark Selected Cards as Printed')
                    ->modalDescription('Mark all selected employee ID cards as Printed (received in office)?')
                    ->action(function (Collection $records) {
                        $records->each->update([
                            'status' => 'printed',
                            'status_updated_at' => now(),
                        ]);
                        Notification::make()
                            ->title('Marked as Printed')
                            ->body("{$records->count()} cards set to Printed.")
                            ->success()
                            ->send();
                        $this->resetTable();
                    }),

                BulkAction::make('bulkSentForPrint')
                    ->label('Set Status: Sent for Print')
                    ->icon('heroicon-m-arrow-up-tray')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $records->each->update([
                            'status' => 'sent for print',
                            'status_updated_at' => now(),
                        ]);
                        Notification::make()
                            ->title('Updated to Sent for Print')
                            ->body("{$records->count()} cards set to Sent for Print.")
                            ->success()
                            ->send();
                        $this->resetTable();
                    }),

                BulkAction::make('bulkInOffice')
                    ->label('Set Status: In Office')
                    ->icon('heroicon-m-building-office-2')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $records->each->update([
                            'status' => 'in office',
                            'status_updated_at' => now(),
                        ]);
                        Notification::make()
                            ->title('Updated to In Office')
                            ->body("{$records->count()} cards set to In Office.")
                            ->success()
                            ->send();
                        $this->resetTable();
                    }),

                BulkAction::make('bulkReleased')
                    ->label('Set Status: Released')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $records->each->update([
                            'status' => 'released',
                            'status_updated_at' => now(),
                        ]);
                        Notification::make()
                            ->title('Updated to Released')
                            ->body("{$records->count()} cards set to Released.")
                            ->success()
                            ->send();
                        $this->resetTable();
                    }),

                BulkAction::make('bulkOnHold')
                    ->label('Set Status: On Hold')
                    ->icon('heroicon-m-pause-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $records->each->update([
                            'status' => 'on hold',
                            'status_updated_at' => now(),
                        ]);
                        Notification::make()
                            ->title('Updated to On Hold')
                            ->body("{$records->count()} cards set to On Hold.")
                            ->warning()
                            ->send();
                        $this->resetTable();
                    }),

                DeleteBulkAction::make()
                    ->after(function () {
                        $this->record->update([
                            'total_records' => $this->record->items()->count(),
                        ]);
                    }),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('printFullSheet')
                ->label('Print Sheet')
                ->icon('heroicon-m-printer')
                ->color('success')
                ->url(fn () => route('id-card-print-reports.print', [
                    'report' => $this->record->id,
                    'department' => $this->activeDepartment !== 'ALL' ? $this->activeDepartment : null,
                    'status' => $this->activeStatus !== 'ALL' ? $this->activeStatus : null,
                ]))
                ->openUrlInNewTab(),

            Action::make('importCsv')
                ->label('Import CSV')
                ->icon('heroicon-m-arrow-up-tray')
                ->color('info')
                ->modalHeading('Import Employees from CSV / Excel')
                ->modalDescription('Upload a CSV or Excel file containing columns: code, name, depart. This will match employee codes with the master database and import them into this batch.')
                ->schema([
                    FileUpload::make('uploaded_file')
                        ->label('CSV / Excel File (.csv / .xlsx)')
                        ->disk('public')
                        ->directory('id-card-batches')
                        ->acceptedFileTypes([
                            'text/csv',
                            'text/plain',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'application/csv',
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'csv_file_path' => $data['uploaded_file'],
                    ]);

                    $count = app(IdCardPrintProcessingService::class)->processUploadedFile($this->record);

                    Notification::make()
                        ->title('CSV Imported')
                        ->body("Successfully imported {$count} employee records into this batch.")
                        ->success()
                        ->send();

                    $this->resetTable();
                }),

            Action::make('syncDb')
                ->label('Re-sync from Employee DB')
                ->icon('heroicon-m-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Synchronize with Employee Master Data')
                ->modalDescription('This will refresh Names, Departments, and Designations from the master Employee database for all codes in this batch. Card statuses will not be modified. Proceed?')
                ->action(function () {
                    $updated = app(IdCardPrintProcessingService::class)->syncFromEmployeeDb($this->record);
                    Notification::make()
                        ->title('Synced with Employee DB')
                        ->body("Refreshed data for {$updated} employee records.")
                        ->success()
                        ->send();
                    $this->resetTable();
                }),

            EditAction::make(),
        ];
    }
}
