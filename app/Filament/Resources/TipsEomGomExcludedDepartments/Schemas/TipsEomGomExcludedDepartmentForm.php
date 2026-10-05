<?php

namespace App\Filament\Resources\TipsEomGomExcludedDepartments\Schemas;

use App\Models\Department;
use App\Models\TipsEomGomExcludedDepartment;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TipsEomGomExcludedDepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('department_id')
                    ->label('Department to Exclude')
                    ->helperText('Select a department to exclude from the random EOM/GOM monthly report pool. (Note: Gaming and Slot are already reserved for Entry 1).')
                    ->options(fn () => Department::whereNotIn('id', [6, 7])->orderBy('name')->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required()
                    ->unique(TipsEomGomExcludedDepartment::class, 'department_id', ignoreRecord: true),

                Textarea::make('notes')
                    ->label('Reason / Notes')
                    ->placeholder('e.g. Back-office department or excluded by management policy')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
