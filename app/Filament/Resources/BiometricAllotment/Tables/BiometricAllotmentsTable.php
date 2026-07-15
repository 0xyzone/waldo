<?php

namespace App\Filament\Resources\BiometricAllotment\Tables;

use App\Models\Employee;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
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
                    ->color(fn($state) => match ($state) {
                        'Done' => 'success',
                        'Left Job' => 'danger',
                        'Not Done Yet' => 'warning',
                        'Bio Not Required' => 'info',
                    }),
                TextColumn::make('code')
                    ->label('Code')
                    ->fontFamily('mono')
                    ->searchable(),
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
                    ->color(fn($state) => match ($state) {
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
                TextColumn::make('phone')
                    ->label('Phone')
                    ->icon('heroicon-o-phone')
                    ->iconColor('info')
                    ->copyable()
                    ->copyMessage('Phone number copied!')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->orderByRaw('CAST(REGEXP_REPLACE(code, "[^0-9]", "") AS UNSIGNED) DESC'))
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('call')
                    ->label('Call')
                    ->icon('heroicon-o-phone')
                    ->color('info')
                    ->iconButton()
                    ->url(fn($record) => $record->phone ? 'tel:' . $record->phone : null)
                    ->openUrlInNewTab(false)
                    ->visible(fn($record) => filled($record->phone)),
                Action::make('add to employee')
                    ->label('Convert')
                    ->button()
                    ->visible(function ($record) {
                        $auth = auth()->user();
                        if ($auth->hasRole('HR')) {
                            $employee = Employee::where('employee_code', $record->code)->first();
                            return !$employee;
                        }
                        return false;
                    })
                    ->form([
                        TextInput::make('code')
                            ->label('Employee Code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn($record) => $record->code),
                        TextInput::make('name')
                            ->label('Employee Name')
                            ->required()
                            ->default(fn($record) => $record->name),
                        Select::make('department_id')
                            ->relationship('department', 'name')
                            ->label('Department')
                            ->required()
                            ->default(fn($record) => $record->department_id),
                    ])
                    ->action(function (array $data) {
                        $employee = Employee::create([
                            'employee_code' => $data['code'],
                            'name' => $data['name'],
                            'department_id' => $data['department_id'],
                            'employee_status' => 'Active',
                            'tips_status' => 'Release'
                        ]);
                        Notification::make()
                            ->title('Employee Created')
                            ->body('Employee created successfully')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
