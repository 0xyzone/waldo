<?php

namespace App\Filament\Resources\SalaryIncrementRequests\Tables;

use App\Models\SalaryIncrementRequest;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class SalaryIncrementRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('request_number')
                    ->label('Req #')
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->description(fn (SalaryIncrementRequest $record): string => $record->employee_id.' • '.($record->department?->name ?? 'No Dept'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('hod.name')
                    ->label('Recommending HOD')
                    ->description(fn (SalaryIncrementRequest $record): ?string => $record->hod_id)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('date_requested')
                    ->label('Requested')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('date_applicable')
                    ->label('Effective')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('current_salary')
                    ->label('Current')
                    ->money('NPR')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('proposed_salary')
                    ->label('Proposed')
                    ->money('NPR')
                    ->placeholder('—')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('increment_amount')
                    ->label('Increment')
                    ->money('NPR')
                    ->placeholder('—')
                    ->color('success')
                    ->sortable(),

                TextColumn::make('increment_percentage')
                    ->label('Incr %')
                    ->suffix('%')
                    ->placeholder('—')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                IconColumn::make('hr_acknowledged')
                    ->label('HR Ack')
                    ->boolean()
                    ->tooltip(fn (SalaryIncrementRequest $record): string => $record->hr_acknowledged_at
                        ? 'Ack by '.($record->hrAcknowledgedBy?->name ?? 'HR').' on '.$record->hr_acknowledged_at->format('d M Y h:i A')
                        : 'Pending HR Ack')
                    ->color(fn (SalaryIncrementRequest $record): string => $record->hr_acknowledged ? 'success' : 'gray'),

                IconColumn::make('finance_acknowledged')
                    ->label('Finance Ack')
                    ->boolean()
                    ->tooltip(fn (SalaryIncrementRequest $record): string => $record->finance_acknowledged_at
                        ? 'Ack by '.($record->financeAcknowledgedBy?->name ?? 'Finance').' on '.$record->finance_acknowledged_at->format('d M Y h:i A')
                        : 'Pending Finance Ack')
                    ->color(fn (SalaryIncrementRequest $record): string => $record->finance_acknowledged ? 'info' : 'gray'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'hr_acknowledged' => 'warning',
                        'finance_acknowledged' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending Review',
                        'hr_acknowledged' => 'HR Ack',
                        'finance_acknowledged' => 'Finance Ack',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        default => ucfirst($state),
                    }),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending Review',
                        'hr_acknowledged' => 'HR Acknowledged',
                        'finance_acknowledged' => 'Finance Acknowledged',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                TernaryFilter::make('hr_acknowledged')
                    ->label('HR Acknowledged')
                    ->placeholder('All Requests')
                    ->trueLabel('HR Acknowledged')
                    ->falseLabel('Pending HR Ack'),

                TernaryFilter::make('finance_acknowledged')
                    ->label('Finance Acknowledged')
                    ->placeholder('All Requests')
                    ->trueLabel('Finance Acknowledged')
                    ->falseLabel('Pending Finance Ack'),
            ])
            ->recordActions([
                Action::make('hr_acknowledge')
                    ->label('HR Ack')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('HR Acknowledge Salary Increment')
                    ->modalDescription(fn (SalaryIncrementRequest $record): string => "Confirm HR acknowledgement for {$record->employee?->name} ({$record->employee_id}) requested by {$record->hod?->name}.")
                    ->modalSubmitActionLabel('Confirm HR Ack')
                    ->visible(function (SalaryIncrementRequest $record): bool {
                        $user = Auth::user();

                        return ! $record->hr_acknowledged && ($user?->hasRole(['super_admin', 'HR', 'HR Assist']) ?? true);
                    })
                    ->action(function (SalaryIncrementRequest $record): void {
                        $record->update([
                            'hr_acknowledged' => true,
                            'hr_acknowledged_by' => Auth::id(),
                            'hr_acknowledged_at' => now(),
                            'status' => $record->finance_acknowledged ? 'finance_acknowledged' : 'hr_acknowledged',
                        ]);

                        Notification::make()
                            ->title('HR Acknowledged')
                            ->body("Salary increment request for {$record->employee?->name} has been acknowledged by HR.")
                            ->success()
                            ->send();
                    }),

                Action::make('finance_acknowledge')
                    ->label('Finance Ack')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('info')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Finance Acknowledge Salary Increment')
                    ->modalDescription(fn (SalaryIncrementRequest $record): string => "Confirm Finance acknowledgement for {$record->employee?->name} (Proposed Salary: NPR ".number_format((float) $record->proposed_salary, 2).').')
                    ->modalSubmitActionLabel('Confirm Finance Ack')
                    ->visible(function (SalaryIncrementRequest $record): bool {
                        $user = Auth::user();

                        return ! $record->finance_acknowledged && ($user?->hasRole(['Finance', 'super_admin']) ?? true);
                    })
                    ->action(function (SalaryIncrementRequest $record): void {
                        $record->update([
                            'finance_acknowledged' => true,
                            'finance_acknowledged_by' => Auth::id(),
                            'finance_acknowledged_at' => now(),
                            'status' => 'finance_acknowledged',
                        ]);

                        Notification::make()
                            ->title('Finance Acknowledged')
                            ->body("Salary increment request for {$record->employee?->name} has been acknowledged by Finance.")
                            ->success()
                            ->send();
                    }),

                Action::make('print')
                    ->label('Print Form')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (SalaryIncrementRequest $record): string => route('salary-increment-requests.print-record', ['record' => $record->id]))
                    ->openUrlInNewTab(),

                EditAction::make()
                    ->modalWidth('4xl'),

                DeleteAction::make(),
            ], position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_hr_acknowledge')
                        ->label('Bulk HR Acknowledge')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(function (SalaryIncrementRequest $record) {
                                if (! $record->hr_acknowledged) {
                                    $record->update([
                                        'hr_acknowledged' => true,
                                        'hr_acknowledged_by' => Auth::id(),
                                        'hr_acknowledged_at' => now(),
                                        'status' => $record->finance_acknowledged ? 'finance_acknowledged' : 'hr_acknowledged',
                                    ]);
                                }
                            });

                            Notification::make()
                                ->title('HR Acknowledgements Updated')
                                ->body('Selected records have been marked as acknowledged by HR.')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('bulk_finance_acknowledge')
                        ->label('Bulk Finance Acknowledge')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(function (SalaryIncrementRequest $record) {
                                if (! $record->finance_acknowledged) {
                                    $record->update([
                                        'finance_acknowledged' => true,
                                        'finance_acknowledged_by' => Auth::id(),
                                        'finance_acknowledged_at' => now(),
                                        'status' => 'finance_acknowledged',
                                    ]);
                                }
                            });

                            Notification::make()
                                ->title('Finance Acknowledgements Updated')
                                ->body('Selected records have been marked as acknowledged by Finance.')
                                ->success()
                                ->send();
                        }),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
