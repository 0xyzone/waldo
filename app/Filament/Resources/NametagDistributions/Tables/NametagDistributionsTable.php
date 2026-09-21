<?php

namespace App\Filament\Resources\NametagDistributions\Tables;

use App\Models\Department;
use App\Models\Designation;
use App\Models\NametagDistribution;
use App\Services\NametagDistributionExportService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class NametagDistributionsTable
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
                TextColumn::make('date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),
                SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'Pending' => 'Pending',
                        'Printed' => 'Printed',
                        'Released' => 'Released',
                    ])
                    ->selectablePlaceholder(false)
                    ->sortable(),
                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(25)
                    ->tooltip(fn (NametagDistribution $record): ?string => $record->remarks)
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('M d, Y H:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('M d, Y H:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->recordClasses(fn (NametagDistribution $record) => match ($record->status) {
                'Pending' => 'border-l-4 border-amber-500',
                'Printed' => 'border-l-4 border-blue-500',
                'Released' => 'border-l-4 border-emerald-500',
                default => null,
            })
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Pending' => 'Pending',
                        'Printed' => 'Printed',
                        'Released' => 'Released',
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
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('date_from')
                            ->label('From Date')
                            ->native(false),
                        DatePicker::make('date_until')
                            ->label('Until Date')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date)
                            )
                            ->when(
                                $data['date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date)
                            );
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                Action::make('export')
                    ->label('Export Data')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->modalHeading('Export Filtered NameTag Distributions')
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
                            ->label('Apply Status Colors & Formatting (Excel only)')
                            ->default(true),
                        CheckboxList::make('columns')
                            ->label('Select Headers to Include')
                            ->options(NametagDistributionExportService::getAvailableColumns())
                            ->default(array_keys(NametagDistributionExportService::getAvailableColumns()))
                            ->columns(2)
                            ->required()
                            ->bulkToggleable(),
                    ])
                    ->action(function (array $data, HasTable $livewire, NametagDistributionExportService $service) {
                        $records = $livewire
                            ->getFilteredTableQuery()
                            ->with(['employee.department', 'employee.designation'])
                            ->get();

                        return $service->export(
                            $records,
                            $data['columns'] ?? array_keys(NametagDistributionExportService::getAvailableColumns()),
                            $data['format'] ?? 'xlsx',
                            (bool) ($data['apply_styling'] ?? true)
                        );
                    }),
                BulkActionGroup::make([
                    Action::make('exportSelected')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->accessSelectedRecords()
                        ->modalHeading('Export Selected NameTag Distributions')
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
                                ->label('Apply Status Colors & Formatting (Excel only)')
                                ->default(true),
                            CheckboxList::make('columns')
                                ->label('Select Headers to Include')
                                ->options(NametagDistributionExportService::getAvailableColumns())
                                ->default(array_keys(NametagDistributionExportService::getAvailableColumns()))
                                ->columns(2)
                                ->required()
                                ->bulkToggleable(),
                        ])
                        ->action(function (array $data, Collection $records, NametagDistributionExportService $service) {
                            if ($records->isEmpty()) {
                                return null;
                            }

                            $records->loadMissing(['employee.department', 'employee.designation']);

                            return $service->export(
                                $records,
                                $data['columns'] ?? array_keys(NametagDistributionExportService::getAvailableColumns()),
                                $data['format'] ?? 'xlsx',
                                (bool) ($data['apply_styling'] ?? true)
                            );
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
