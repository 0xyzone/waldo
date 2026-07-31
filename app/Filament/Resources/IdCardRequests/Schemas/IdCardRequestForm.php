<?php

namespace App\Filament\Resources\IdCardRequests\Schemas;

use App\Models\Employee;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class IdCardRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'lg' => 3])
                    ->schema([
                        // Left / Primary 2 Columns
                        Grid::make(1)
                            ->schema([
                                Section::make('Employee Details')
                                    ->icon('heroicon-o-user-circle')
                                    ->description('Select an existing employee or enter details manually.')
                                    ->schema([
                                        Checkbox::make('source')
                                            ->label('Select from Existing Employee Database')
                                            ->formatStateUsing(fn ($record, $state) => $record ? $record->source === 'employee' : (bool) $state)
                                            ->dehydrateStateUsing(fn ($state) => $state ? 'employee' : 'custom')
                                            ->live()
                                            ->afterStateUpdated(function (bool $state, Set $set) {
                                                if (! $state) {
                                                    $set('selected_employee_code', null);
                                                }
                                            }),

                                        Select::make('selected_employee_code')
                                            ->label('Search & Autofill Employee')
                                            ->options(function () {
                                                return Employee::with(['department', 'designation'])
                                                    ->get()
                                                    ->mapWithKeys(function (Employee $emp) {
                                                        $name = $emp->name ?: trim(($emp->first_name ?? '').' '.($emp->last_name ?? ''));

                                                        return [$emp->employee_code => "{$name} ({$emp->employee_code})"];
                                                    });
                                            })
                                            ->formatStateUsing(fn ($record) => $record?->employee_code)
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->visible(fn (Get $get) => (bool) $get('source'))
                                            ->dehydrated(false)
                                            ->afterStateUpdated(function (?string $state, Set $set) {
                                                if (! $state) {
                                                    return;
                                                }
                                                $emp = Employee::with(['department', 'designation'])->find($state);
                                                if ($emp) {
                                                    $name = $emp->name ?: trim(($emp->first_name ?? '').' '.($emp->last_name ?? ''));
                                                    $set('employee_code', $emp->employee_code);
                                                    $set('employee_name', $name);
                                                    $set('employee_designation', $emp->designation?->name);
                                                    $set('employee_department', $emp->department?->name);
                                                }
                                            }),

                                        Grid::make(['default' => 1, 'sm' => 2])
                                            ->schema([
                                                TextInput::make('employee_code')
                                                    ->label('Employee Code')
                                                    ->placeholder('e.g. EMP001')
                                                    ->maxLength(255),

                                                TextInput::make('employee_name')
                                                    ->label('Employee Name')
                                                    ->placeholder('Full Name')
                                                    ->required()
                                                    ->maxLength(255),

                                                TextInput::make('employee_designation')
                                                    ->label('Designation')
                                                    ->placeholder('e.g. Software Engineer')
                                                    ->maxLength(255),

                                                TextInput::make('employee_department')
                                                    ->label('Department')
                                                    ->placeholder('e.g. IT & Software')
                                                    ->maxLength(255),
                                            ]),
                                    ]),

                                Section::make('Additional Information')
                                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                                    ->schema([
                                        Textarea::make('notes')
                                            ->label('Notes for IT Team')
                                            ->placeholder('Add specific details or instructions regarding this reprint request...')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columnSpan(['lg' => 2]),

                        // Right Sidebar Column (1 Column)
                        Grid::make(1)
                            ->schema([
                                Section::make('Request Status & Info')
                                    ->icon('heroicon-o-document-text')
                                    ->schema([
                                        Select::make('status')
                                            ->label('Record Status')
                                            ->options([
                                                'pending' => 'Pending',
                                                'designed' => 'Designed',
                                                'sent for print' => 'Sent for Print',
                                                'done' => 'Done',
                                            ])
                                            ->default('pending')
                                            ->required()
                                            ->native(false),

                                        Placeholder::make('created_at')
                                            ->label('Created Date')
                                            ->content(fn ($record) => $record?->created_at?->format('F j, Y h:i A') ?? '-')
                                            ->hidden(fn (string $operation) => $operation === 'create'),

                                        Placeholder::make('updated_at')
                                            ->label('Last Updated')
                                            ->content(fn ($record) => $record?->updated_at?->format('F j, Y h:i A') ?? '-')
                                            ->hidden(fn (string $operation) => $operation === 'create'),
                                    ]),
                            ])
                            ->columnSpan(['lg' => 1]),

                        // Full Width Action Log Section
                        Section::make('Action Log History')
                            ->icon('heroicon-o-clock')
                            ->description('Automated history of status changes and actions recorded by IT staff.')
                            ->schema([
                                Repeater::make('actionLogs')
                                    ->relationship('actionLogs')
                                    ->schema([
                                        Grid::make(['default' => 1, 'sm' => 4])
                                            ->schema([
                                                TextInput::make('user_name')
                                                    ->label('Done By')
                                                    ->disabled(),

                                                TextInput::make('to_status')
                                                    ->label('Status Set')
                                                    ->disabled(),

                                                TextInput::make('action_description')
                                                    ->label('Action Log')
                                                    ->disabled(),

                                                DateTimePicker::make('created_at')
                                                    ->label('Timestamp')
                                                    ->disabled(),
                                            ]),
                                    ])
                                    ->addable(false)
                                    ->deletable(false)
                                    ->reorderable(false)
                                    ->columnSpanFull(),
                            ])
                            ->hidden(fn (string $operation) => $operation === 'create')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
