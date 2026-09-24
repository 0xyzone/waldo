<?php

namespace App\Filament\Resources\NametagFines\Tables;

use App\Models\Department;
use App\Models\Designation;
use App\Models\NametagFine;
use App\Models\User;
use App\Services\NametagFineExportService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class NametagFinesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee_id')
                    ->label('Code')
                    ->badge()
                    ->color('primary')
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.name')
                    ->label('Employee Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),
                TextColumn::make('employee.department.name')
                    ->label('Department')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('employee.designation.name')
                    ->label('Designation')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('reason')
                    ->label('Reason')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Lost' => 'danger',
                        'Damaged' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Fine')
                    ->money('NPR')
                    ->weight('bold')
                    ->sortable(),
                TextColumn::make('for_month')
                    ->label('Target Month')
                    ->formatStateUsing(fn($state, NametagFine $record): string => ucfirst((string) $state) . ' ' . $record->for_year)
                    ->sortable(),
                TextColumn::make('creator.name')
                    ->label('Initiated By')
                    ->badge()
                    ->color('gray')
                    ->placeholder('System')
                    ->sortable(),
                IconColumn::make('acknowledged')
                    ->label('Acknowledged')
                    ->boolean()
                    ->tooltip(function (NametagFine $record): string {
                        if (!$record->acknowledged) {
                            return 'Pending Acknowledgement';
                        }
                        $by = $record->acknowledger?->name ? " by {$record->acknowledger->name}" : '';
                        $when = $record->acknowledged_at ? ' on ' . $record->acknowledged_at->format('M d, Y h:i A') : '';

                        return "Acknowledged{$by}{$when}";
                    })
                    ->color(fn(NametagFine $record): string => $record->acknowledged ? 'success' : 'gray'),
                TextColumn::make('acknowledged_at')
                    ->label('Acknowledged At')
                    ->dateTime('M d, Y h:i A')
                    ->placeholder('Pending')
                    ->color(fn(NametagFine $record): string => $record->acknowledged ? 'success' : 'gray')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('acknowledger.name')
                    ->label('Acknowledged By')
                    ->badge()
                    ->color(fn(NametagFine $record): string => $record->acknowledged ? 'success' : 'gray')
                    ->placeholder('Pending')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('finance_acknowledged')
                    ->label('Finance Ack.')
                    ->boolean()
                    ->tooltip(function (NametagFine $record): string {
                        if (!$record->finance_acknowledged) {
                            return 'Pending Finance Acknowledgement';
                        }
                        $by = $record->financeAcknowledger?->name ? " by {$record->financeAcknowledger->name}" : '';
                        $when = $record->finance_acknowledged_at ? ' on ' . $record->finance_acknowledged_at->format('M d, Y h:i A') : '';

                        return "Finance Acknowledged{$by}{$when}";
                    })
                    ->color(fn(NametagFine $record): string => $record->finance_acknowledged ? 'success' : 'gray'),
                TextColumn::make('financeAcknowledger.name')
                    ->label('Finance Ack. By')
                    ->badge()
                    ->color(fn(NametagFine $record): string => $record->finance_acknowledged ? 'success' : 'gray')
                    ->placeholder('Pending')
                    ->sortable(),
                TextColumn::make('finance_acknowledged_at')
                    ->label('Finance Ack. Time')
                    ->dateTime('M d, Y h:i A')
                    ->placeholder('Pending')
                    ->color(fn(NametagFine $record): string => $record->finance_acknowledged ? 'success' : 'gray')
                    ->sortable(),
                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(20)
                    ->tooltip(fn(NametagFine $record): ?string => $record->remarks)
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('M d, Y H:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->recordClasses(fn(NametagFine $record) => match (true) {
                $record->finance_acknowledged && $record->acknowledged => 'border-l-4 border-emerald-500',
                $record->finance_acknowledged => 'border-l-4 border-blue-500',
                $record->acknowledged => 'border-l-4 border-teal-500',
                default => 'border-l-4 border-amber-500',
            })
            ->filters([
                TernaryFilter::make('acknowledged')
                    ->label('HR Acknowledgement')
                    ->placeholder('All Records')
                    ->trueLabel('Acknowledged Only')
                    ->falseLabel('Pending Only'),
                TernaryFilter::make('finance_acknowledged')
                    ->label('Finance Acknowledgement')
                    ->placeholder('All Records')
                    ->trueLabel('Acknowledged Only')
                    ->falseLabel('Pending Only'),
                SelectFilter::make('reason')
                    ->label('Reason')
                    ->options([
                        'Lost' => 'Lost',
                        'Damaged' => 'Damaged',
                        'Other' => 'Other',
                    ])
                    ->native(false),
                SelectFilter::make('for_month')
                    ->label('Month')
                    ->options([
                        'january' => 'January',
                        'february' => 'February',
                        'march' => 'March',
                        'april' => 'April',
                        'may' => 'May',
                        'june' => 'June',
                        'july' => 'July',
                        'august' => 'August',
                        'september' => 'September',
                        'october' => 'October',
                        'november' => 'November',
                        'december' => 'December',
                    ])
                    ->native(false),
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(fn() => Department::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn(Builder $query, $deptId) => $query->whereHas('employee', fn(Builder $q) => $q->where('department_id', $deptId))
                        );
                    })
                    ->searchable()
                    ->preload(),
                SelectFilter::make('designation_id')
                    ->label('Designation')
                    ->options(fn() => Designation::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn(Builder $query, $desigId) => $query->whereHas('employee', fn(Builder $q) => $q->where('designation_id', $desigId))
                        );
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Action::make('acknowledge')
                    ->label('HR Ack')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Acknowledge NameTag Fine')
                    ->modalDescription(fn(NametagFine $record): string => "Confirm acknowledgement of the {$record->reason} nametag fine for {$record->employee?->name} ({$record->employee_id}) for {$record->for_month} {$record->for_year}.")
                    ->modalSubmitActionLabel('Confirm Acknowledge')
                    ->visible(function (NametagFine $record): bool {
                        /** @var User|null $user */
                        $user = Auth::user();

                        return !$record->acknowledged && ($user?->hasRole(['super_admin', 'HR', 'HR Assist']) ?? false);
                    })
                    ->action(function (NametagFine $record): void {
                        $record->update([
                            'acknowledged' => true,
                            'acknowledged_by' => Auth::id(),
                            'acknowledged_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Fine Acknowledged')
                            ->body("NameTag fine for {$record->employee?->name} ({$record->employee_id}) has been acknowledged.")
                            ->success()
                            ->send();
                    }),
                Action::make('finance_acknowledge')
                    ->label('Finance Ack')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('info')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Finance Acknowledge NameTag Fine')
                    ->modalDescription(fn(NametagFine $record): string => "Confirm finance acknowledgement of NPR {$record->amount} ({$record->reason}) for {$record->employee?->name} ({$record->employee_id}) for {$record->for_month} {$record->for_year}.")
                    ->modalSubmitActionLabel('Confirm Finance Acknowledge')
                    ->visible(function (NametagFine $record): bool {
                        /** @var User|null $user */
                        $user = Auth::user();

                        return !$record->finance_acknowledged && ($user?->hasRole(['Finance', 'super_admin']) ?? false);
                    })
                    ->action(function (NametagFine $record): void {
                        $record->update([
                            'finance_acknowledged' => true,
                            'finance_acknowledged_by' => Auth::id(),
                            'finance_acknowledged_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Finance Acknowledged')
                            ->body("NameTag fine for {$record->employee?->name} ({$record->employee_id}) has been acknowledged by Finance.")
                            ->success()
                            ->send();
                    }),
                EditAction::make()
                    ->iconSize('lg')
                    ->hiddenLabel(),
                DeleteAction::make()
                    ->iconSize('lg')
                    ->hiddenLabel(),
            ], position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                Action::make('export')
                    ->label('Export Data')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->modalHeading('Export Filtered NameTag Fines')
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
                            ->label('Apply Acknowledged Status Colors & Formatting (Excel only)')
                            ->default(true),
                        CheckboxList::make('columns')
                            ->label('Select Headers to Include')
                            ->options(NametagFineExportService::getAvailableColumns())
                            ->default(array_keys(NametagFineExportService::getAvailableColumns()))
                            ->columns(2)
                            ->required()
                            ->bulkToggleable(),
                    ])
                    ->action(function (array $data, HasTable $livewire, NametagFineExportService $service) {
                        $records = $livewire
                            ->getFilteredTableQuery()
                            ->with(['employee.department', 'employee.designation', 'creator', 'acknowledger'])
                            ->get();

                        return $service->export(
                            $records,
                            $data['columns'] ?? array_keys(NametagFineExportService::getAvailableColumns()),
                            $data['format'] ?? 'xlsx',
                            (bool) ($data['apply_styling'] ?? true)
                        );
                    }),
                BulkActionGroup::make([
                    Action::make('financeAcknowledgeSelected')
                        ->label('Finance Acknowledge Selected')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Finance Acknowledge Selected NameTag Fines')
                        ->modalDescription('Confirm finance acknowledgement of all selected pending nametag fines.')
                        ->modalSubmitActionLabel('Confirm Finance Acknowledge')
                        ->visible(function (): bool {
                            /** @var User|null $user */
                            $user = Auth::user();

                            return $user?->hasRole(['Finance', 'super_admin']) ?? false;
                        })
                        ->action(function (Collection $records): void {
                            $pendingRecords = $records->filter(fn(NametagFine $record): bool => !$record->finance_acknowledged);

                            if ($pendingRecords->isEmpty()) {
                                Notification::make()
                                    ->title('Nothing to Acknowledge')
                                    ->body('All selected records are already acknowledged by Finance.')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            $count = $pendingRecords->count();

                            NametagFine::whereIn('id', $pendingRecords->pluck('id'))->update([
                                'finance_acknowledged' => true,
                                'finance_acknowledged_by' => Auth::id(),
                                'finance_acknowledged_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Fines Finance-Acknowledged')
                                ->body("{$count} nametag fine(s) have been acknowledged by Finance.")
                                ->success()
                                ->send();
                        }),
                    Action::make('acknowledgeSelected')
                        ->label('Acknowledge Selected')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Acknowledge Selected NameTag Fines')
                        ->modalDescription('Confirm acknowledgement of all selected pending nametag fines.')
                        ->modalSubmitActionLabel('Confirm Acknowledge')
                        ->visible(function (): bool {
                            /** @var User|null $user */
                            $user = Auth::user();

                            return $user?->hasRole(['super_admin', 'HR', 'HR Assist']) ?? false;
                        })
                        ->action(function (Collection $records): void {
                            $pendingRecords = $records->filter(fn(NametagFine $record): bool => !$record->acknowledged);

                            if ($pendingRecords->isEmpty()) {
                                Notification::make()
                                    ->title('Nothing to Acknowledge')
                                    ->body('All selected records are already acknowledged.')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            $count = $pendingRecords->count();

                            NametagFine::whereIn('id', $pendingRecords->pluck('id'))->update([
                                'acknowledged' => true,
                                'acknowledged_by' => Auth::id(),
                                'acknowledged_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Fines Acknowledged')
                                ->body("{$count} nametag fine(s) have been acknowledged.")
                                ->success()
                                ->send();
                        }),
                    Action::make('exportSelected')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->accessSelectedRecords()
                        ->modalHeading('Export Selected NameTag Fines')
                        ->modalDescription('Choose file format and select header columns to include in your report.')
                        ->modalSubmitActionLabel('Download Selected')
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
                                ->label('Apply Acknowledged Status Colors & Formatting (Excel only)')
                                ->default(true),
                            CheckboxList::make('columns')
                                ->label('Select Headers to Include')
                                ->options(NametagFineExportService::getAvailableColumns())
                                ->default(array_keys(NametagFineExportService::getAvailableColumns()))
                                ->columns(2)
                                ->required()
                                ->bulkToggleable(),
                        ])
                        ->action(function (array $data, Collection $records, NametagFineExportService $service) {
                            if ($records->isEmpty()) {
                                return null;
                            }

                            $records->loadMissing(['employee.department', 'employee.designation', 'creator', 'acknowledger']);

                            return $service->export(
                                $records,
                                $data['columns'] ?? array_keys(NametagFineExportService::getAvailableColumns()),
                                $data['format'] ?? 'xlsx',
                                (bool) ($data['apply_styling'] ?? true)
                            );
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
