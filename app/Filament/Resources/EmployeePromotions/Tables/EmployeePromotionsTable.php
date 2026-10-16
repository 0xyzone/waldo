<?php

namespace App\Filament\Resources\EmployeePromotions\Tables;

use App\Jobs\SyncBulkPromotionsToSheetJob;
use App\Jobs\SyncPromotionToSheetJob;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeePromotion;
use App\Services\EmployeePromotionExportService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmployeePromotionsTable
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
                TextColumn::make('promotion_date')
                    ->label('Promotion Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('changes')
                    ->label('Changes')
                    ->getStateUsing(function (EmployeePromotion $record): string {
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
                    ->tooltip(fn (EmployeePromotion $record) => $record->acknowledged_at ? $record->acknowledged_at->format('d-M-Y h:i A') : 'Not Acknowledged')
                    ->color(fn (EmployeePromotion $record) => $record->acknowledged ? 'success' : 'gray'),
                IconColumn::make('hrms_synced')
                    ->label('HRMS Synced')
                    ->boolean()
                    ->tooltip(fn (EmployeePromotion $record) => $record->hrms_synced_at ? $record->hrms_synced_at->format('d-M-Y h:i A') : 'Not Synced')
                    ->color(fn (EmployeePromotion $record) => $record->hrms_synced ? 'info' : 'gray'),
                TextColumn::make('remarks')
                    ->limit(30)
                    ->tooltip(fn (EmployeePromotion $record) => $record->remarks)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->recordClasses(fn (EmployeePromotion $record) => match (true) {
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
                Filter::make('promotion_date')
                    ->form([
                        DatePicker::make('promotion_date_from')
                            ->label('Promoted From')
                            ->native(false),
                        DatePicker::make('promotion_date_to')
                            ->label('Promoted To')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['promotion_date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('promotion_date', '>=', $date)
                            )
                            ->when(
                                $data['promotion_date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('promotion_date', '<=', $date)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['promotion_date_from'] ?? null) {
                            $indicators[] = 'Promoted from '.Carbon::parse($data['promotion_date_from'])->toFormattedDateString();
                        }
                        if ($data['promotion_date_to'] ?? null) {
                            $indicators[] = 'Promoted until '.Carbon::parse($data['promotion_date_to'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
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
                    ->modalHeading('Acknowledge Promotion')
                    ->modalDescription(fn (EmployeePromotion $record) => "Confirm acknowledgement of the promotion for {$record->employee?->name} ({$record->employee_id}).")
                    ->modalSubmitActionLabel('Acknowledge')
                    ->visible(fn (EmployeePromotion $record): bool => ! $record->acknowledged)
                    ->action(function (EmployeePromotion $record): void {
                        $record->update([
                            'acknowledged' => true,
                            'acknowledged_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Promotion Acknowledged')
                            ->body("Promotion for {$record->employee_id} has been acknowledged.")
                            ->success()
                            ->send();
                    }),
                Action::make('markHrmsSynced')
                    ->label(fn (EmployeePromotion $record) => $record->hrms_synced ? 'Mark HRMS Unsynced' : 'Mark HRMS Synced')
                    ->icon(fn (EmployeePromotion $record) => $record->hrms_synced ? 'heroicon-o-arrow-uturn-left' : 'heroicon-o-computer-desktop')
                    ->color(fn (EmployeePromotion $record) => $record->hrms_synced ? 'gray' : 'info')
                    ->button()
                    ->visible(fn () => Auth::user()->hasRole(['super_admin', 'HR']))
                    ->requiresConfirmation()
                    ->modalHeading(fn (EmployeePromotion $record) => $record->hrms_synced ? 'Unmark HRMS Sync' : 'Mark as HRMS Synced')
                    ->modalDescription(fn (EmployeePromotion $record) => $record->hrms_synced
                        ? "This will mark the HRMS sync as incomplete for {$record->employee_id}."
                        : "Confirm that you have manually updated HRMS for {$record->employee_id}.")
                    ->action(function (EmployeePromotion $record): void {
                        $isSyncing = ! $record->hrms_synced;
                        $record->update([
                            'hrms_synced' => $isSyncing,
                            'hrms_synced_at' => $isSyncing ? now() : null,
                        ]);
                        $employee = Employee::find($record->employee_id);
                        if ($employee) {
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
                        }

                        // Dispatch background Google Sheets sync
                        $userId = Auth::id();
                        SyncPromotionToSheetJob::dispatch($record->id, $userId, $isSyncing)->onConnection('database');

                        // Ensure background queue worker executes if no daemon is running
                        static::dispatchBackgroundQueueWorker();

                        Notification::make()
                            ->title($isSyncing ? 'Marked as HRMS Synced' : 'HRMS Sync Unmarked')
                            ->body($isSyncing
                                ? "Promotion for {$record->employee_id} marked as synced. Google Sheet update is running in the background."
                                : "Promotion for {$record->employee_id} HRMS sync status has been reset.")
                            ->color($isSyncing ? 'info' : 'warning')
                            ->send();
                    }),
                Action::make('printCongratulations')
                    ->label('Congratulation Note')
                    ->icon('heroicon-o-printer')
                    ->color('warning')
                    ->tooltip('Print Congratulatory Note (A4 Letterhead)')
                    ->url(fn (EmployeePromotion $record): string => route('employee-promotions.print-congratulations', ['ids' => $record->id]))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                Action::make('exportExcel')
                    ->label('Download Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->modalHeading('Export Promotions for HRMS')
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
                    ->action(function (array $data, HasTable $livewire, EmployeePromotionExportService $service) {
                        $promotions = $livewire
                            ->getFilteredTableQuery()
                            ->with(['toDepartment', 'toDesignation', 'employee'])
                            ->get();

                        if ($promotions->isEmpty()) {
                            Notification::make()
                                ->title('No records to export')
                                ->warning()
                                ->send();

                            return null;
                        }

                        $uploadedTemplatePath = ! empty($data['sample_file'])
                            ? Storage::disk('public')->path($data['sample_file'])
                            : null;

                        return $service->export($promotions, $uploadedTemplatePath);
                    }),
                BulkActionGroup::make([
                    Action::make('bulkAcknowledge')
                        ->label('Acknowledge Selected')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->accessSelectedRecords()
                        ->requiresConfirmation()
                        ->modalHeading('Acknowledge Selected Promotions')
                        ->modalDescription('Are you sure you want to mark all selected promotions as acknowledged?')
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
                                    ->title('Promotions Acknowledged')
                                    ->body("Successfully marked {$count} promotion(s) as acknowledged.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Already Acknowledged')
                                    ->body('All selected promotions were already acknowledged.')
                                    ->info()
                                    ->send();
                            }
                        }),
                    Action::make('bulkMarkHrmsSynced')
                        ->label('Mark HRMS Synced')
                        ->icon('heroicon-o-computer-desktop')
                        ->color('info')
                        ->visible(fn () => Auth::user()->hasRole(['super_admin', 'HR']))
                        ->accessSelectedRecords()
                        ->requiresConfirmation()
                        ->modalHeading('Mark Selected Promotions as HRMS Synced')
                        ->modalDescription('Confirm that you have updated HRMS for all selected promotions. This will update the employee department and designation, and sync to Google Sheets.')
                        ->modalSubmitActionLabel('Mark Selected as Synced')
                        ->action(function (Collection $records) {
                            $syncedIds = [];
                            $count = 0;
                            $userId = Auth::id();

                            foreach ($records as $record) {
                                if (! $record->hrms_synced) {
                                    $record->update([
                                        'hrms_synced' => true,
                                        'hrms_synced_at' => now(),
                                    ]);

                                    $employee = Employee::find($record->employee_id);
                                    if ($employee) {
                                        $employee->update([
                                            'department_id' => $record->to_department_id,
                                            'designation_id' => $record->to_designation_id,
                                        ]);
                                    }

                                    $syncedIds[] = $record->id;
                                    $count++;
                                }
                            }

                            if ($count > 0) {
                                SyncBulkPromotionsToSheetJob::dispatch($syncedIds, $userId, true)->onConnection('database');
                                static::dispatchBackgroundQueueWorker();

                                Notification::make()
                                    ->title('Promotions Marked as HRMS Synced')
                                    ->body("Successfully marked {$count} promotion(s) as HRMS synced. Google Sheets sync is running in the background.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Already Synced')
                                    ->body('All selected promotions were already marked as HRMS synced.')
                                    ->info()
                                    ->send();
                            }
                        }),
                    Action::make('bulkUnmarkHrmsSynced')
                        ->label('Unmark HRMS Synced')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('gray')
                        ->visible(fn () => Auth::user()->hasRole(['super_admin', 'HR']))
                        ->accessSelectedRecords()
                        ->requiresConfirmation()
                        ->modalHeading('Unmark HRMS Sync for Selected Promotions')
                        ->modalDescription('This will mark the HRMS sync as incomplete for all selected promotions and revert employee department/designation.')
                        ->modalSubmitActionLabel('Unmark Selected')
                        ->action(function (Collection $records) {
                            $unmarkedIds = [];
                            $count = 0;
                            $userId = Auth::id();

                            foreach ($records as $record) {
                                if ($record->hrms_synced) {
                                    $record->update([
                                        'hrms_synced' => false,
                                        'hrms_synced_at' => null,
                                    ]);

                                    $employee = Employee::find($record->employee_id);
                                    if ($employee) {
                                        $employee->update([
                                            'department_id' => $record->from_department_id,
                                            'designation_id' => $record->from_designation_id,
                                        ]);
                                    }

                                    $unmarkedIds[] = $record->id;
                                    $count++;
                                }
                            }

                            if ($count > 0) {
                                SyncBulkPromotionsToSheetJob::dispatch($unmarkedIds, $userId, false)->onConnection('database');
                                static::dispatchBackgroundQueueWorker();

                                Notification::make()
                                    ->title('HRMS Sync Unmarked')
                                    ->body("Successfully unmarked HRMS sync for {$count} promotion(s).")
                                    ->warning()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Not Synced')
                                    ->body('None of the selected promotions were marked as HRMS synced.')
                                    ->info()
                                    ->send();
                            }
                        }),
                    Action::make('printCongratulations')
                        ->label('Print Congratulation Note')
                        ->icon('heroicon-o-printer')
                        ->color('warning')
                        ->accessSelectedRecords()
                        ->action(function (Collection $records, HasTable $livewire) {
                            if ($records->isEmpty()) {
                                Notification::make()
                                    ->title('No records selected')
                                    ->warning()
                                    ->send();

                                return null;
                            }

                            $ids = $records->pluck('id')->join(',');
                            $url = route('employee-promotions.print-congratulations', ['ids' => $ids]);

                            $livewire->js("window.open('{$url}', '_blank');");
                        }),
                    Action::make('exportSelectedExcel')
                        ->label('Download Selected Excel')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->accessSelectedRecords()
                        ->modalHeading('Export Selected Promotions for HRMS')
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
                        ->action(function (array $data, Collection $records, EmployeePromotionExportService $service) {
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

    /**
     * Dispatch background queue worker asynchronously to process queued jobs
     * without blocking the HTTP request or requiring a persistent daemon on local environments.
     */
    public static function dispatchBackgroundQueueWorker(): void
    {
        try {
            $php = PHP_BINARY;
            $artisan = base_path('artisan');

            if (DIRECTORY_SEPARATOR === '\\') {
                pclose(popen("start /B \"\" \"{$php}\" \"{$artisan}\" queue:work database --stop-when-empty > NUL 2>&1", 'r'));
            } else {
                exec("\"{$php}\" \"{$artisan}\" queue:work database --stop-when-empty > /dev/null 2>&1 &");
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to spawn background queue worker: '.$e->getMessage());
        }
    }
}
