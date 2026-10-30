<?php

namespace App\Filament\Resources\EmployeeSuspensions\Schemas;

use App\Filament\Components\ActionLogSection;
use App\Models\Employee;
use App\Models\EmployeeSuspension;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class EmployeeSuspensionForm
{
    /**
     * @var array<string, Employee|null>
     */
    protected static array $employeeCache = [];

    protected static function resolveSelectedEmployee(Get $get, ?EmployeeSuspension $record): ?Employee
    {
        $code = $get('employee_id') ?? $record?->employee_id;
        if (! $code) {
            return null;
        }

        $key = strtoupper(trim((string) $code));
        if (! array_key_exists($key, self::$employeeCache)) {
            self::$employeeCache[$key] = Employee::with(['department', 'designation'])->find($code);
        }

        return self::$employeeCache[$key];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Selected Employee Profile')
                    ->description('Employee snapshot retrieved from database.')
                    ->icon('heroicon-o-user-circle')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2, 'lg' => 4])
                            ->schema([
                                TextEntry::make('info_code')
                                    ->label('Employee Code')
                                    ->badge()
                                    ->color('primary')
                                    ->fontFamily('mono')
                                    ->placeholder('—')
                                    ->state(fn (Get $get, ?EmployeeSuspension $record) => self::resolveSelectedEmployee($get, $record)?->employee_code),

                                TextEntry::make('info_name')
                                    ->label('Full Name')
                                    ->weight('bold')
                                    ->placeholder('—')
                                    ->state(fn (Get $get, ?EmployeeSuspension $record) => self::resolveSelectedEmployee($get, $record)?->name),

                                TextEntry::make('info_department')
                                    ->label('Department')
                                    ->badge()
                                    ->color('info')
                                    ->placeholder('—')
                                    ->state(fn (Get $get, ?EmployeeSuspension $record) => self::resolveSelectedEmployee($get, $record)?->department?->name),

                                TextEntry::make('info_designation')
                                    ->label('Designation')
                                    ->badge()
                                    ->color('gray')
                                    ->placeholder('—')
                                    ->state(fn (Get $get, ?EmployeeSuspension $record) => self::resolveSelectedEmployee($get, $record)?->designation?->name),

                                TextEntry::make('info_status')
                                    ->label('Employee Status')
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'Active' => 'success',
                                        'Suspended' => 'warning',
                                        'Resigned', 'Terminated' => 'danger',
                                        default => 'gray',
                                    })
                                    ->placeholder('—')
                                    ->state(fn (Get $get, ?EmployeeSuspension $record) => self::resolveSelectedEmployee($get, $record)?->employee_status),

                                TextEntry::make('info_tips_status')
                                    ->label('Tips Status')
                                    ->badge()
                                    ->color(fn (?string $state): string => $state === 'Release' ? 'success' : 'danger')
                                    ->placeholder('—')
                                    ->state(fn (Get $get, ?EmployeeSuspension $record) => self::resolveSelectedEmployee($get, $record)?->tips_status),

                                TextEntry::make('info_join_date')
                                    ->label('Join Date')
                                    ->placeholder('—')
                                    ->state(fn (Get $get, ?EmployeeSuspension $record) => self::resolveSelectedEmployee($get, $record)?->join_date_formatted),

                                TextEntry::make('info_contact')
                                    ->label('Contact Number')
                                    ->fontFamily('mono')
                                    ->placeholder('—')
                                    ->state(fn (Get $get, ?EmployeeSuspension $record) => self::resolveSelectedEmployee($get, $record)?->contact_number),
                            ]),
                    ])
                    ->visible(fn (Get $get, ?EmployeeSuspension $record): bool => filled($get('employee_id') ?? $record?->employee_id)),

                Section::make('Suspension Details')
                    ->description('Specify the employee and suspension duration.')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 3])
                            ->schema([
                                Select::make('employee_id')
                                    ->label('Employee')
                                    ->relationship(
                                        name: 'employee',
                                        titleAttribute: 'name'
                                    )
                                    ->getOptionLabelFromRecordUsing(function (Employee $record): string {
                                        $dept = $record->department?->name ? " [{$record->department->name}]" : '';

                                        return strtoupper($record->employee_code).' | '.$record->name.$dept;
                                    })
                                    ->searchable(['name', 'employee_code'])
                                    ->preload()
                                    ->live()
                                    ->required()
                                    ->columnSpan(['sm' => 3]),

                                DatePicker::make('start_date')
                                    ->label('Suspended From')
                                    ->native(false)
                                    ->default(now())
                                    ->required(),

                                DatePicker::make('end_date')
                                    ->label('Suspended To')
                                    ->native(false)
                                    ->default(now()->addDays(7))
                                    ->afterOrEqual('start_date')
                                    ->required(),

                                Select::make('status')
                                    ->label('Suspension Status')
                                    ->options([
                                        'active' => 'Active',
                                        'completed' => 'Completed / Uplifted',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->default('active')
                                    ->required()
                                    ->native(false),
                            ]),

                        Textarea::make('reason')
                            ->label('Reason for Suspension')
                            ->placeholder('Describe the incident, breach of policy, or reason for suspension...')
                            ->rows(3)
                            ->columnSpanFull()
                            ->required(),
                    ]),

                Section::make('Related Attachments & Documents')
                    ->description('Upload suspension letters, evidence, notices, or related files.')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('attachments')
                            ->label('Attachments')
                            ->multiple()
                            ->reorderable()
                            ->openable()
                            ->downloadable()
                            ->previewable()
                            ->directory('suspension-attachments')
                            ->disk('public')
                            ->maxSize(10240) // 10MB per file
                            ->columnSpanFull(),
                    ]),
                ActionLogSection::make(),
            ]);
    }
}
