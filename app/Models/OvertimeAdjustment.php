<?php

namespace App\Models;

use App\Traits\HasActionLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeAdjustment extends Model
{
    use HasActionLogs;

    protected $fillable = [
        'overtime_report_id',
        'employee_id',
        'ot_type',
        'adjustment_date',
        'ot_block_id',
        'hours',
        'remarks',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'adjustment_date' => 'date',
            'hours' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (OvertimeAdjustment $adjustment): void {
            if (! $adjustment->created_by && auth()->check()) {
                $adjustment->created_by = auth()->id();
            }
        });
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(OvertimeReport::class, 'overtime_report_id');
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(OtBlock::class, 'ot_block_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
