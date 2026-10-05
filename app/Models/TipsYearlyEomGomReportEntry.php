<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipsYearlyEomGomReportEntry extends Model
{
    protected $fillable = [
        'report_id',
        'month_number',
        'month_name',
        'entry_number',
        'is_gaming_slot',
        'department_id',
        'department_name',
        'eom_employee_code_1',
        'eom_employee_code_2',
        'gom_employee_code_1',
        'eom_remarks_1',
        'eom_remarks_2',
        'gom_remarks_1',
    ];

    protected function casts(): array
    {
        return [
            'month_number' => 'integer',
            'entry_number' => 'integer',
            'is_gaming_slot' => 'boolean',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(TipsYearlyEomGomReport::class, 'report_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function eomEmployee1(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'eom_employee_code_1', 'employee_code');
    }

    public function eomEmployee2(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'eom_employee_code_2', 'employee_code');
    }

    public function gomEmployee1(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'gom_employee_code_1', 'employee_code');
    }

    public function getEvaluatedMonthNameAttribute(): string
    {
        return match ($this->month_number) {
            1 => 'December',
            2 => 'January',
            3 => 'February',
            4 => 'March',
            5 => 'April',
            6 => 'May',
            7 => 'June',
            8 => 'July',
            9 => 'August',
            10 => 'September',
            11 => 'October',
            12 => 'November',
            default => 'December',
        };
    }

    public function getEvaluatedYearAttribute(): int
    {
        $baseYear = (int) ($this->report?->year ?? date('Y'));

        return $this->month_number === 1 ? ($baseYear - 1) : $baseYear;
    }

    public function getPeriodLabelAttribute(): string
    {
        return "{$this->evaluated_month_name} {$this->evaluated_year}";
    }
}
