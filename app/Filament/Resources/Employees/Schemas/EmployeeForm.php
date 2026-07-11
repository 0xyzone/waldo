<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('employee_code')
                    ->label('Employee Code')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->email(),
                TextInput::make('gender'),
                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('designation_id')
                    ->relationship('designation', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('employee_status')
                    ->label('Employee Status'),
                DatePicker::make('join_date')
                    ->label('Join Date'),
                TextInput::make('contact_number')
                    ->label('Contact Number'),
            ]);
    }
}
