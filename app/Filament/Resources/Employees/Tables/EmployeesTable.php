<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Models\Employee;
use App\Services\GoogleSheetsService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                            ->color('gray')
                            ->grow(false),
                        TextColumn::make('employee_status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Active' => 'success',
                                'Inactive' => 'gray',
                                'Resigned' => 'danger',
                                'Resigning this month' => 'warning',
                                'Terminated' => 'danger',
                                default => 'gray',
                            })
                            ->grow(false),
                    ])->extraAttributes(['class' => 'justify-between items-center']),

                    TextColumn::make('name')
                        ->searchable()
                        ->sortable()
                        ->weight('bold')
                        ->size('lg')
                        ->extraAttributes(['class' => 'mt-3 block']),

                    TextColumn::make('department.name')
                        ->icon('heroicon-m-building-office-2')
                        ->color('gray')
                        ->size('sm')
                        ->extraAttributes(['class' => 'mt-1 block']),

                    TextColumn::make('designation.name')
                        ->icon('heroicon-m-briefcase')
                        ->color('gray')
                        ->size('sm')
                        ->extraAttributes(['class' => 'mt-1 block']),

                    TextColumn::make('join_date')
                        ->icon('heroicon-m-calendar')
                        ->date()
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
                'Resigned' => 'bg-rose-row border-rose-200 dark:border-rose-900',
                'Resigning this month' => 'bg-violet-row border-violet-200 dark:border-violet-900',
                'Terminated' => 'bg-red-row border-red-200 dark:border-red-900',
                default => null,
            })
            ->filters([
                //
            ])
            ->defaultSort('employee_code', 'asc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                // Action::make('syncToGoogleSheet')
                //     ->label('Sync to Google Sheet')
                //     ->icon('heroicon-o-arrow-path')
                //     ->color('success')
                //     ->action(function (Employee $record, GoogleSheetsService $service) {
                //         $service->syncEmployee($record);

                //         Notification::make()
                //             ->title('Synced successfully')
                //             ->body("Employee {$record->employee_code} was successfully synced to Google Sheets.")
                //             ->success()
                //             ->send();
                //     }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
