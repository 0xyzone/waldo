<?php

namespace App\Filament\Resources\EmployeeTransfers\Tables;

use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeTransfer;
use App\Services\EmployeeTransferExportService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmployeeTransfersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee_id')
                    ->label('Employee Code')
                    ->copyable()
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.name')
                    ->label('Name')
                    ->copyable()
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('transfer_date')
                    ->label('Transfer Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('changes')
                    ->label('Changes')
                    ->getStateUsing(function (EmployeeTransfer $record): string {
                        $changes = [];
                        if ($record->from_department_id !== $record->to_department_id) {
                            $changes[] = '<span>'.$record->fromDepartment?->name.'</span> <span class="text-gray-500"> -> </span> <span class="text-green-500">'.$record->toDepartment?->name.'</span>';
                        }
                        if ($record->from_designation_id !== $record->to_designation_id) {
                            $changes[] = '<span>'.$record->fromDesignation?->name.'</span> <span class="text-gray-500"> -> </span> <span class="text-green-500">'.$record->toDesignation?->name.'</span>';
                        }

                        return implode(' | ', $changes);
                    })
                    ->html(),
                IconColumn::make('acknowledged')
                    ->label('Acknowledged')
                    ->boolean()
                    ->tooltip(fn (EmployeeTransfer $record) => $record->acknowledged_at ? $record->acknowledged_at->format('d-M-Y h:i A') : 'Not Acknowledged')
                    ->color(fn (EmployeeTransfer $record) => $record->acknowledged ? 'success' : 'gray'),
                IconColumn::make('hrms_synced')
                    ->label('HRMS Synced')
                    ->boolean()
                    ->tooltip(fn (EmployeeTransfer $record) => $record->hrms_synced_at ? $record->hrms_synced_at->format('d-M-Y h:i A') : 'Not Synced')
                    ->color(fn (EmployeeTransfer $record) => $record->hrms_synced ? 'info' : 'gray'),
                TextColumn::make('remarks')
                    ->limit(30)
                    ->tooltip(fn (EmployeeTransfer $record) => $record->remarks)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->recordClasses(fn (EmployeeTransfer $record) => match (true) {
                $record->acknowledged && $record->hrms_synced => 'bg-emerald-950 border-emerald-200 dark:border-emerald-900',
                $record->acknowledged => 'bg-amber-950 border-amber-200 dark:border-amber-900',
                default => null,
            })
            ->filters([
                SelectFilter::make('to_department_id')
                    ->label('New Department')
                    ->options(fn () => Department::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload(),
                SelectFilter::make('to_designation_id')
                    ->label('New Designation')
                    ->options(fn () => Designation::active()->ordered()->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('acknowledged')
                    ->label('Acknowledged')
                    ->placeholder('All Records')
                    ->trueLabel('Acknowledged')
                    ->falseLabel('Not Acknowledged'),
                TernaryFilter::make('hrms_synced')
                    ->label('HRMS Synced')
                    ->placeholder('All Records')
                    ->trueLabel('HRMS Synced')
                    ->falseLabel('HRMS Not Synced'),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth('3xl'),
                Action::make('acknowledge')
                    ->label('Acknowledge')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Acknowledge Transfer')
                    ->modalDescription(fn (EmployeeTransfer $record) => "Confirm acknowledgement of the transfer for {$record->employee?->name} ({$record->employee_id}).")
                    ->modalSubmitActionLabel('Acknowledge')
                    ->visible(fn (EmployeeTransfer $record): bool => ! $record->acknowledged)
                    ->action(function (EmployeeTransfer $record): void {
                        $record->update([
                            'acknowledged' => true,
                            'acknowledged_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Transfer Acknowledged')
                            ->body("Transfer for {$record->employee_id} has been acknowledged.")
                            ->success()
                            ->send();
                    }),
                Action::make('markHrmsSynced')
                    ->label(fn (EmployeeTransfer $record) => $record->hrms_synced ? 'Mark HRMS Unsynced' : 'Mark HRMS Synced')
                    ->icon(fn (EmployeeTransfer $record) => $record->hrms_synced ? 'heroicon-o-arrow-uturn-left' : 'heroicon-o-computer-desktop')
                    ->color(fn (EmployeeTransfer $record) => $record->hrms_synced ? 'gray' : 'info')
                    ->button()
                    ->visible(fn () => Auth::user()->hasRole(['super_admin', 'HR']))
                    ->requiresConfirmation()
                    ->modalHeading(fn (EmployeeTransfer $record) => $record->hrms_synced ? 'Unmark HRMS Sync' : 'Mark as HRMS Synced')
                    ->modalDescription(fn (EmployeeTransfer $record) => $record->hrms_synced
                        ? "This will mark the HRMS sync as incomplete for {$record->employee_id}."
                        : "Confirm that you have manually updated HRMS for {$record->employee_id}.")
                    ->action(function (EmployeeTransfer $record): void {
                        $isSyncing = ! $record->hrms_synced;
                        $record->update([
                            'hrms_synced' => $isSyncing,
                            'hrms_synced_at' => $isSyncing ? now() : null,
                        ]);
                        $employee = Employee::find($record->employee_id);
                        if ($isSyncing) {
                            $employee->update([
                                'department_id' => $record->to_department_id,
                                'designation_id' => $record->to_designation_id,
                            ]);
                        } else {
                            $employee->update([
                                'department_id' => $record->from_department_id,
                                'designation_id' => $record->from_designation_id,
                            ]);
                        }

                        Notification::make()
                            ->title($isSyncing ? 'Marked as HRMS Synced' : 'HRMS Sync Unmarked')
                            ->body($isSyncing
                                ? "Transfer for {$record->employee_id} has been marked as synced to HRMS."
                                : "Transfer for {$record->employee_id} HRMS sync status has been reset.")
                            ->color($isSyncing ? 'info' : 'warning')
                            ->send();
                    }),
            ])
            ->toolbarActions([
                Action::make('exportExcel')
                    ->label('Download Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->modalHeading('Export Transfers for HRMS')
                    ->modalDescription('Download an Excel import file matching the HRMS format. Optionally upload the latest HRMS sample template to update reference sheets and dropdowns.')
                    ->modalSubmitActionLabel('Download Excel')
                    ->modalWidth('lg')
                    ->form([
                        FileUpload::make('sample_file')
                            ->label('Upload Latest Sample File (.xlsx)')
                            ->helperText('Optional. Upload the latest change-type-import-sample.xlsx from HRMS to update reference sheets, or leave empty to use the current template.')
                            ->disk('public')
                            ->directory('change-type-templates')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'application/octet-stream',
                                'application/zip',
                                'application/x-zip-compressed',
                            ])
                            ->maxSize(15360),
                    ])
                    ->action(function (array $data, HasTable $livewire, EmployeeTransferExportService $service) {
                        $transfers = $livewire
                            ->getFilteredTableQuery()
                            ->with(['toDepartment', 'toDesignation', 'employee'])
                            ->get();

                        if ($transfers->isEmpty()) {
                            Notification::make()
                                ->title('No records to export')
                                ->warning()
                                ->send();

                            return null;
                        }

                        $uploadedTemplatePath = ! empty($data['sample_file'])
                            ? Storage::disk('public')->path($data['sample_file'])
                            : null;

                        return $service->export($transfers, $uploadedTemplatePath);
                    }),
                BulkActionGroup::make([
                    Action::make('bulkAcknowledge')
                        ->label('Acknowledge Selected')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->accessSelectedRecords()
                        ->requiresConfirmation()
                        ->modalHeading('Acknowledge Selected Transfers')
                        ->modalDescription('Are you sure you want to mark all selected transfers as acknowledged?')
                        ->modalSubmitActionLabel('Acknowledge Selected')
                        ->action(function (Collection $records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if (! $record->acknowledged) {
                                    $record->update([
                                        'acknowledged' => true,
                                        'acknowledged_at' => now(),
                                    ]);
                                    $count++;
                                }
                            }

                            if ($count > 0) {
                                Notification::make()
                                    ->title('Transfers Acknowledged')
                                    ->body("Successfully marked {$count} transfer(s) as acknowledged.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Already Acknowledged')
                                    ->body('All selected transfers were already acknowledged.')
                                    ->info()
                                    ->send();
                            }
                        }),
                    Action::make('exportSelectedExcel')
                        ->label('Download Selected Excel')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->accessSelectedRecords()
                        ->modalHeading('Export Selected Transfers for HRMS')
                        ->modalDescription('Download an Excel import file matching the HRMS format. Optionally upload the latest HRMS sample template to update reference sheets and dropdowns.')
                        ->modalSubmitActionLabel('Download Excel')
                        ->modalWidth('lg')
                        ->form([
                            FileUpload::make('sample_file')
                                ->label('Upload Latest Sample File (.xlsx)')
                                ->helperText('Optional. Upload the latest change-type-import-sample.xlsx from HRMS to update reference sheets, or leave empty to use the current template.')
                                ->disk('public')
                                ->directory('change-type-templates')
                                ->acceptedFileTypes([
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    'application/vnd.ms-excel',
                                    'application/octet-stream',
                                    'application/zip',
                                    'application/x-zip-compressed',
                                ])
                                ->maxSize(15360),
                        ])
                        ->action(function (array $data, Collection $records, EmployeeTransferExportService $service) {
                            if ($records->isEmpty()) {
                                Notification::make()
                                    ->title('No records selected')
                                    ->warning()
                                    ->send();

                                return null;
                            }

                            $records->loadMissing(['toDepartment', 'toDesignation', 'employee']);

                            $uploadedTemplatePath = ! empty($data['sample_file'])
                                ? Storage::disk('public')->path($data['sample_file'])
                                : null;

                            return $service->export($records, $uploadedTemplatePath);
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
