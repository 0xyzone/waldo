<?php

namespace App\Filament\Resources\ManualAttendanceEmployees\Schemas;

use App\Models\Employee;
use App\Models\ManualAttendanceEmployee;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ManualAttendanceEmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Manual Attendance Employee')
                    ->description('Designate employees whose attendance is tracked manually instead of automated biometrics.')
                    ->schema([
                        Grid::make(1)->schema([
                            Select::make('employee_code')
                                ->label('Select Employee')
                                ->options(function (?ManualAttendanceEmployee $record): array {
                                    $existingCodes = ManualAttendanceEmployee::query()
                                        ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                        ->pluck('employee_code')
                                        ->toArray();

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
                                ->live()
                                ->disabled(fn (string $operation): bool => $operation === 'edit')
                                ->required(),
                        ]),

                        Grid::make(3)->schema([
                            Placeholder::make('name_preview')
                                ->label('Full Name')
                                ->content(function (Get $get, ?ManualAttendanceEmployee $record): string {
                                    $code = $get('employee_code') ?? $record?->employee_code;
                                    if (! $code) {
                                        return '—';
                                    }
                                    $emp = Employee::find($code);

                                    return $emp?->name ?? '—';
                                }),

                            Placeholder::make('dept_preview')
                                ->label('Department')
                                ->content(function (Get $get, ?ManualAttendanceEmployee $record): string {
                                    $code = $get('employee_code') ?? $record?->employee_code;
                                    if (! $code) {
                                        return '—';
                                    }
                                    $emp = Employee::with('department')->find($code);

                                    return $emp?->department?->name ?? '—';
                                }),

                            Placeholder::make('desig_preview')
                                ->label('Designation')
                                ->content(function (Get $get, ?ManualAttendanceEmployee $record): string {
                                    $code = $get('employee_code') ?? $record?->employee_code;
                                    if (! $code) {
                                        return '—';
                                    }
                                    $emp = Employee::with('designation')->find($code);

                                    return $emp?->designation?->name ?? '—';
                                }),
                        ]),

                        Toggle::make('is_active')
                            ->label('Active for Manual Attendance')
                            ->default(true)
                            ->helperText('When enabled, this employee will be automatically populated into new monthly manual roster checklists.'),

                        Textarea::make('notes')
                            ->label('Notes / Remarks')
                            ->placeholder('e.g. Special schedule, kitchen staff, remote manual attendance')
                            ->rows(3),
                    ]),
            ]);
    }
}
