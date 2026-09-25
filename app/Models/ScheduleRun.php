<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleRun extends Model
{
    protected $fillable = [
        'command',
        'status',
        'started_at',
        'finished_at',
        'duration_seconds',
        'exit_code',
        'output',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'duration_seconds' => 'decimal:2',
            'exit_code' => 'integer',
        ];
    }
}
