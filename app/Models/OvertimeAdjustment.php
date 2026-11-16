<?php

namespace App\Models;

use App\Traits\HasActionLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeAdjustment extends Model
{
    use HasActionLogs;
    use HasActionLogs;

    protected $fillable = [
        'overtime_report_id',
        'employee_id',
        'normal_adjustment',
        'special_adjustment',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'normal_adjustment' => 'decimal:2',
            'special_adjustment' => 'decimal:2',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(OvertimeReport::class, 'overtime_report_id');
    }
}
