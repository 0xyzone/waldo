<?php

namespace App\Filament\Resources\NametagFines\Tables;

use App\Models\Department;
use App\Models\Designation;
use App\Models\NametagFine;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

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
                    ->color(fn (string $state): string => match ($state) {
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
                    ->formatStateUsing(fn ($state, NametagFine $record): string => ucfirst((string) $state).' '.$record->for_year)
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
                        if (! $record->acknowledged) {
                            return 'Pending Acknowledgement';
                        }
                        $by = $record->acknowledger?->name ? " by {$record->acknowledger->name}" : '';
                        $when = $record->acknowledged_at ? ' on '.$record->acknowledged_at->format('M d, Y h:i A') : '';

                        return "Acknowledged{$by}{$when}";
                    })
                    ->color(fn (NametagFine $record): string => $record->acknowledged ? 'success' : 'gray'),

                TextColumn::make('acknowledged_at')
                    ->label('Acknowledged At')
                    ->dateTime('M d, Y h:i A')
                    ->placeholder('Pending')
                    ->color(fn (NametagFine $record): string => $record->acknowledged ? 'success' : 'gray')
                    ->sortable(),

                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(20)
                    ->tooltip(fn (NametagFine $record): ?string => $record->remarks)
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('M d, Y H:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->recordClasses(fn (NametagFine $record) => match ($record->acknowledged) {
                true => 'border-l-4 border-emerald-500',
                false => 'border-l-4 border-amber-500',
            })
            ->filters([
                TernaryFilter::make('acknowledged')
                    ->label('Acknowledgement Status')
                    ->placeholder('All Records')
                    ->trueLabel('Acknowledged Only')
                    ->falseLabel('Pending Acknowledgement Only'),

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
                    ->options(fn () => Department::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $deptId) => $query->whereHas('employee', fn (Builder $q) => $q->where('department_id', $deptId))
                        );
                    })
                    ->searchable()
                    ->preload(),

                SelectFilter::make('designation_id')
                    ->label('Designation')
                    ->options(fn () => Designation::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $desigId) => $query->whereHas('employee', fn (Builder $q) => $q->where('designation_id', $desigId))
                        );
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Action::make('acknowledge')
                    ->label('Acknowledge')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->button()
                    ->requiresConfirmation()
                    ->modalHeading('Acknowledge NameTag Fine')
                    ->modalDescription(fn (NametagFine $record): string => "Confirm acknowledgement of the {$record->reason} nametag fine for {$record->employee?->name} ({$record->employee_id}) for {$record->for_month} {$record->for_year}.")
                    ->modalSubmitActionLabel('Confirm Acknowledge')
                    ->visible(function (NametagFine $record): bool {
                        /** @var User|null $user */
                        $user = Auth::user();

                        return ! $record->acknowledged && ($user?->hasRole(['super_admin', 'HR', 'HR Assist']) ?? false);
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

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
