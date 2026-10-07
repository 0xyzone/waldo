<?php

namespace App\Filament\Resources\Designations\Tables;

use App\Models\Department;
use App\Models\Employee;
use App\Services\DesignationExportService;
use App\Services\DesignationImportService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class DesignationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('department.name')
                    ->label('Department')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('name')
                    ->label('Designation')
                    ->copyable()
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('rank')
                    ->label('#')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->width('50px'),
                TextColumn::make('active_employees_count')
                    ->label('Active Staff')
                    ->getStateUsing(fn ($record) => Employee::where('designation_id', $record->id)
                        ->where('employee_status', 'Active')
                        ->count())
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->alignCenter(),
                IconColumn::make('job_description')
                    ->label('JD Added?')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->job_description !== null)
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('department_id', 'asc')
            ->filters([
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(Department::where('is_active', true)->orderBy('rank')->pluck('name', 'id'))
                    ->searchable()
                    ->placeholder('All Departments'),
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All Designations')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->slideover(),
            ])
            ->toolbarActions([
                CreateAction::make(),
                Action::make('copyNames')
                    ->label('Copy Names')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('gray')
                    ->tooltip('Copy filtered designation names to clipboard as a line-separated list')
                    ->action(function (HasTable $livewire) {
                        $names = $livewire->getFilteredTableQuery()
                            ->orderBy('name')
                            ->pluck('name')
                            ->filter()
                            ->values();

                        if ($names->isEmpty()) {
                            Notification::make()
                                ->title('No Designations Found')
                                ->warning()
                                ->body('There are no designations matching the current filters.')
                                ->send();

                            return;
                        }

                        $count = $names->count();
                        $text = $names->join("\n");
                        $jsonText = json_encode($text, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

                        $livewire->js("window.navigator.clipboard.writeText({$jsonText});");

                        Notification::make()
                            ->title("Copied {$count} Designations")
                            ->body("List of {$count} designation names copied to clipboard.")
                            ->success()
                            ->send();
                    }),
                Action::make('export')
                    ->label('Export Data')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->modalHeading('Export Filtered Designations')
                    ->modalDescription('Choose file format and select header columns to include in your export report.')
                    ->modalSubmitActionLabel('Download Report')
                    ->form([
                        Radio::make('format')
                            ->label('Export Format')
                            ->options([
                                'xlsx' => 'Excel Spreadsheet (.xlsx) — formatted with status styling',
                                'csv' => 'CSV File (.csv) — plain text data',
                            ])
                            ->default('xlsx')
                            ->required(),
                        Toggle::make('apply_styling')
                            ->label('Apply Status Highlight & Colors (Excel only)')
                            ->default(true),
                        CheckboxList::make('columns')
                            ->label('Select Columns to Include')
                            ->options(DesignationExportService::getAvailableColumns())
                            ->default(array_keys(DesignationExportService::getAvailableColumns()))
                            ->columns(3)
                            ->required()
                            ->bulkToggleable(),
                    ])
                    ->action(function (array $data, HasTable $livewire, DesignationExportService $service) {
                        $records = $livewire->getFilteredTableQuery()
                            ->with(['department', 'employees'])
                            ->get();

                        return $service->export(
                            $records,
                            $data['columns'] ?? array_keys(DesignationExportService::getAvailableColumns()),
                            $data['format'] ?? 'xlsx',
                            (bool) ($data['apply_styling'] ?? true)
                        );
                    }),
                Action::make('import')
                    ->label('Import CSV / JD')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('info')
                    ->modalHeading('Import Job Descriptions from CSV / Excel')
                    ->modalDescription('Upload a CSV or Excel (.xlsx) file linking job designations with job descriptions.')
                    ->modalSubmitActionLabel('Start Import')
                    ->extraModalFooterActions([
                        Action::make('downloadSample')
                            ->label('Download Sample CSV')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('gray')
                            ->action(fn (DesignationImportService $service) => $service->downloadSampleTemplate()),
                    ])
                    ->form([
                        FileUpload::make('file')
                            ->label('CSV or Excel File (.csv / .xlsx)')
                            ->disk('public')
                            ->directory('designations-imports')
                            ->acceptedFileTypes([
                                'text/csv',
                                'text/plain',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'application/csv',
                            ])
                            ->required()
                            ->helperText('Supports columns: Designation, Job Description, Department (optional)'),
                        Toggle::make('overwrite_existing')
                            ->label('Overwrite existing job descriptions')
                            ->helperText('When enabled, existing job descriptions will be replaced. When disabled, only empty descriptions are updated.')
                            ->default(true),
                    ])
                    ->action(function (array $data, DesignationImportService $service) {
                        try {
                            $result = $service->importFile($data['file'], (bool) ($data['overwrite_existing'] ?? true));

                            $msg = "Imported {$result['total_rows']} rows: updated {$result['updated']} designation(s)";
                            if ($result['skipped'] > 0) {
                                $msg .= ", skipped {$result['skipped']} (already had JD)";
                            }
                            if ($result['unmatched'] > 0) {
                                $msg .= ", unmatched {$result['unmatched']}";
                                if (! empty($result['unmatched_names'])) {
                                    $msg .= ' ['.implode(', ', $result['unmatched_names']).']';
                                }
                            }

                            Notification::make()
                                ->title('Import Completed')
                                ->body($msg)
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Import Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    Action::make('copySelectedNames')
                        ->label('Copy Selected Names')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->action(function (Collection $records, HasTable $livewire) {
                            $names = $records->pluck('name')->filter()->values();
                            if ($names->isEmpty()) {
                                Notification::make()->title('No names to copy')->warning()->send();

                                return;
                            }
                            $count = $names->count();
                            $text = $names->join("\n");
                            $jsonText = json_encode($text, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
                            $livewire->js("window.navigator.clipboard.writeText({$jsonText});");
                            Notification::make()
                                ->title("Copied {$count} Selected Designations")
                                ->body("{$count} designation names copied to clipboard.")
                                ->success()
                                ->send();
                        }),
                    Action::make('exportSelected')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->modalHeading('Export Selected Designations')
                        ->modalSubmitActionLabel('Download Report')
                        ->form([
                            Radio::make('format')
                                ->label('Export Format')
                                ->options([
                                    'xlsx' => 'Excel Spreadsheet (.xlsx)',
                                    'csv' => 'CSV File (.csv)',
                                ])
                                ->default('xlsx')
                                ->required(),
                            Toggle::make('apply_styling')
                                ->label('Apply Status Highlight & Colors (Excel only)')
                                ->default(true),
                            CheckboxList::make('columns')
                                ->label('Select Columns to Include')
                                ->options(DesignationExportService::getAvailableColumns())
                                ->default(array_keys(DesignationExportService::getAvailableColumns()))
                                ->columns(3)
                                ->required()
                                ->bulkToggleable(),
                        ])
                        ->action(function (array $data, Collection $records, DesignationExportService $service) {
                            return $service->export(
                                $records->loadMissing(['department', 'employees']),
                                $data['columns'] ?? array_keys(DesignationExportService::getAvailableColumns()),
                                $data['format'] ?? 'xlsx',
                                (bool) ($data['apply_styling'] ?? true)
                            );
                        }),
                ]),
            ]);
    }
}
