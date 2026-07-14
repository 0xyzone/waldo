<?php

namespace App\Filament\Resources\BiometricAllotment\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BiometricAllotmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Done' => 'success',
                        'Left Job' => 'danger',
                        'Not Done Yet' => 'warning',
                        'Bio Not Required' => 'info',
                    }),
                TextColumn::make('code')
                    ->label('Code')
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        $driver = $query->getConnection()->getDriverName();
                        if ($driver === 'sqlite') {
                            return $query->orderByRaw('CAST(SUBSTR(code, 4) AS INTEGER) ' . $direction);
                        }

                        return $query->orderByRaw('CAST(SUBSTR(code, 4) AS UNSIGNED) ' . $direction);
                    }),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('department.name')
                    ->label('Department'),
                TextColumn::make('enrolled_date')
                    ->date(),
                TextColumn::make('set_by')
                    ->label('Set By')
                    ->limit(10)
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Shuraz' => 'info',
                        'Saugat' => 'danger',
                        'Suraj Raj Karmacharya' => 'warning'
                    }),
                IconColumn::make('old_checkout_device')
                    ->label('Old CO Device')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('new_checkin')
                    ->label('New Checkin')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('new_checkout')
                    ->label('New Checkout')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('shift')
                    ->label('Shift'),
                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(20)
                    ->tooltip(fn($state) => $state),
            ])
            ->defaultSort('code', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('code', 'asc');
    }
}
