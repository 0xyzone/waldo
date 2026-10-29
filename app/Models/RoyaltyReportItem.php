<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoyaltyReportItem extends Model
{
    protected $fillable = [
        'royalty_report_id',
        'employee_code',
        'employee_name',
        'department',
        'department_id',
        'designation',
        'designation_id',
        'employee_status',
        'annual_budget',
        'monthly_max_payout',
        'monthly_min_payout',
        'is_exempt',
        'absent_days',
        'late_early_count',
        'missing_thumb_count',
        'performance_deduction_percent',
        'performance_deduction_amount',
        'attendance_deduction_amount',
        'total_deduction_amount',
        'final_payout',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'annual_budget' => 'decimal:2',
            'monthly_max_payout' => 'decimal:2',
            'monthly_min_payout' => 'decimal:2',
            'is_exempt' => 'boolean',
            'absent_days' => 'decimal:2',
            'late_early_count' => 'integer',
            'missing_thumb_count' => 'integer',
            'performance_deduction_percent' => 'decimal:2',
            'performance_deduction_amount' => 'decimal:2',
            'attendance_deduction_amount' => 'decimal:2',
            'total_deduction_amount' => 'decimal:2',
            'final_payout' => 'decimal:2',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(RoyaltyReport::class, 'royalty_report_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_code', 'employee_code');
    }

    public function departmentModel(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function designationModel(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }
}
