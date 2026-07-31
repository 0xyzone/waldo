<?php

namespace App\Filament\Resources\TerminatedEmployees\Pages;

use App\Filament\Resources\TerminatedEmployeeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTerminatedEmployee extends CreateRecord
{
    protected static string $resource = TerminatedEmployeeResource::class;
}
