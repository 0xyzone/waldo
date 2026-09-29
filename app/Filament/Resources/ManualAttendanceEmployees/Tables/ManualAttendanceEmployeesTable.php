<?php

namespace App\Filament\Resources\ManualAttendanceEmployees\Tables;

use App\Models\Employee;
use App\Models\ManualAttendanceEmployee;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class ManualAttendanceEmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('#')
                    ->rowIndex(),

                TextColumn::make('employee_code')
                    ->label('Employee Code')
                    ->fontFamily('mono')
                    ->copyable()
                    ->copyMessage('Employee code copied')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('employee.name')
                    ->label('Employee Name')
                    ->sortable()
                    ->searchable()
                    ->weight('semibold'),

                TextColumn::make('employee.department.name')
                    ->label('Department')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('employee.designation.name')
                    ->label('Designation')
                    ->placeholder('—')
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->onColor('success')
                    ->offColor('gray')
                    ->afterStateUpdated(function (ManualAttendanceEmployee $record, bool $state): void {
                        Notification::make()
                            ->title('Status Updated')
                            ->body("{$record->employee?->name} ({$record->employee_code}) marked as ".($state ? 'Active' : 'Inactive').'.')
                            ->success()
                            ->send();
                    }),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->placeholder('—')
                    ->limit(30)
                    ->tooltip(fn (?string $state): ?string => $state),

                TextColumn::make('created_at')
                    ->label('Added Date')
                    ->dateTime('d M, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active Only',
                        '0' => 'Inactive Only',
                    ]),

                SelectFilter::make('department')
                    ->label('Department')
                    ->relationship('employee.department', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                Action::make('bulk_add')
                    ->label('Bulk Add Employees')
                    ->icon('heroicon-m-user-plus')
                    ->color('primary')
                    ->modalHeading('Add Multiple Employees to Manual Attendance')
                    ->modalDescription('Select one or more employees to mark them as manual attendance employees.')
                    ->schema([
                        Select::make('employee_codes')
                            ->label('Select Employees')
                            ->multiple()
                            ->options(function (): array {
                                $existingCodes = ManualAttendanceEmployee::pluck('employee_code')->toArray();

                                return Employee::query()
                                    ->with(['department', 'designation'])
                                    ->whereNotIn('employee_code', $existingCodes)
                                    ->orderBy('employee_code')
                                    ->get()
                                    ->mapWithKeys(function (Employee $emp): array {
                                        $dept = $emp->department?->name ?? 'No Dept';
                                        $desig = $emp->designation?->name ? " • {$emp->designation->name}" : '';

                                        return [$emp->employee_code => "{$emp->employee_code} — {$emp->name} ({$dept}{$desig})"];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->extraAlpineAttributes([
                                'x-init' => <<<'JS'
                                    const setupSearchClear = () => {
                                        if (typeof select === 'undefined' || !select) {
                                            setTimeout(setupSearchClear, 30);
                                            return;
                                        }
                                        if (select._searchClearHooked) {
                                            return;
                                        }
                                        select._searchClearHooked = true;

                                        const resetSearchInput = () => {
                                            if (select && select.searchInput) {
                                                select.searchInput.value = '';
                                                select.searchQuery = '';
                                                if (!select.hasDynamicOptions && select.originalOptions) {
                                                    select.options = JSON.parse(JSON.stringify(select.originalOptions));
                                                }
                                                select.renderOptions();
                                                if (typeof select.deferPositionDropdown === 'function') {
                                                    select.deferPositionDropdown();
                                                }
                                                select.searchInput.focus();
                                            }
                                        };

                                        const origSelectOption = select.selectOption.bind(select);
                                        select.selectOption = function (value) {
                                            const wasSelected = Array.isArray(select.state) && select.state.includes(value);
                                            origSelectOption(value);
                                            if (!wasSelected) {
                                                resetSearchInput();
                                            }
                                        };
                                    };
                                    $nextTick(setupSearchClear);
                                    $watch('state', (newVal, oldVal) => {
                                        if (Array.isArray(newVal) && (!Array.isArray(oldVal) || newVal.length > oldVal.length)) {
                                            if (typeof select !== 'undefined' && select && select.searchInput && select.searchInput.value) {
                                                select.searchInput.value = '';
                                                select.searchQuery = '';
                                                if (!select.hasDynamicOptions && select.originalOptions) {
                                                    select.options = JSON.parse(JSON.stringify(select.originalOptions));
                                                }
                                                select.renderOptions();
                                                if (typeof select.deferPositionDropdown === 'function') {
                                                    select.deferPositionDropdown();
                                                }
                                                select.searchInput.focus();
                                            }
                                        }
                                    });
                                JS,
                            ]),

                        Textarea::make('notes')
                            ->label('Notes for Selected Employees (Optional)')
                            ->placeholder('e.g. Added in batch for manual attendance')
                            ->rows(2),
                    ])
                    ->action(function (array $data): void {
                        $codes = $data['employee_codes'] ?? [];
                        $notes = $data['notes'] ?? null;
                        $userId = Auth::id();
                        $count = 0;

                        foreach ($codes as $code) {
                            ManualAttendanceEmployee::firstOrCreate(
                                ['employee_code' => $code],
                                [
                                    'is_active' => true,
                                    'notes' => $notes,
                                    'created_by' => $userId,
                                ]
                            );
                            $count++;
                        }

                        Notification::make()
                            ->title('Employees Added')
                            ->body("Successfully added {$count} employee(s) as manual attendance employees.")
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->label('Remove')
                    ->modalHeading('Remove from Manual Attendance')
                    ->modalDescription('Are you sure you want to remove this employee from manual attendance tracking?'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_active')
                        ->label('Mark as Active')
                        ->icon('heroicon-m-check')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each(fn (ManualAttendanceEmployee $r) => $r->update(['is_active' => true]));
                            Notification::make()
                                ->title('Updated')
                                ->body("{$records->count()} employee(s) marked as active.")
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('mark_inactive')
                        ->label('Mark as Inactive')
                        ->icon('heroicon-m-x-mark')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each(fn (ManualAttendanceEmployee $r) => $r->update(['is_active' => false]));
                            Notification::make()
                                ->title('Updated')
                                ->body("{$records->count()} employee(s) marked as inactive.")
                                ->warning()
                                ->send();
                        }),

                    DeleteBulkAction::make()
                        ->label('Remove Selected'),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }
}
