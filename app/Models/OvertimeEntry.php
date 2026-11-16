<?php

namespace App\Models;

use App\Traits\HasActionLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeEntry extends Model
{
    use HasActionLogs;

    protected $fillable = [
        'employee_id',
        'ot_date',
        'ot_block_id',
        'hours',
        'is_special_ot',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'ot_date' => 'date',
            'hours' => 'decimal:2',
            'is_special_ot' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (OvertimeEntry $entry): void {
            if ($entry->ot_date) {
                $entry->is_special_ot = OtSpecialDate::whereDate('date', $entry->ot_date)->exists();
            }
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_code');
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(OtBlock::class, 'ot_block_id');
    }
}
