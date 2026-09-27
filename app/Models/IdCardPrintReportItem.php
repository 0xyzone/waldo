<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdCardPrintReportItem extends Model
{
    protected $fillable = [
        'id_card_print_report_id',
        'employee_code',
        'employee_name',
        'department',
        'designation',
        'status',
        'csv_name',
        'csv_department',
        'notes',
        'status_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'status_updated_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(IdCardPrintReport::class, 'id_card_print_report_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_code', 'employee_code');
    }

    /**
     * Scope query to order by numeric portion of employee code.
     */
    public function scopeOrderByNumericCode(Builder $query, string $direction = 'asc'): Builder
    {
        $dir = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';

        return $query
            ->orderByRaw('CASE WHEN employee_code IS NULL OR employee_code = "" THEN 1 ELSE 0 END ASC')
            ->orderByRaw('CAST(NULLIF(REGEXP_REPLACE(employee_code, "[^0-9]", ""), "") AS UNSIGNED) '.$dir);
    }
}
