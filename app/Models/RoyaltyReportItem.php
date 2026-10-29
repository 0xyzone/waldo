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
        'join_date',
        'tenure_year',
        'tenure_start',
        'tenure_end',
        'tenure_month',
        'starting_tenure_budget',
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
        'consumed_tenure_budget',
        'remaining_budget',
        'payout_status',
        'held_reason',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'tenure_start' => 'date',
            'tenure_end' => 'date',
            'tenure_year' => 'integer',
            'tenure_month' => 'integer',
            'starting_tenure_budget' => 'decimal:2',
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
            'consumed_tenure_budget' => 'decimal:2',
            'remaining_budget' => 'decimal:2',
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

    public function getDepartmentNameAttribute(): ?string
    {
        return $this->department;
    }

    public function getDesignationNameAttribute(): ?string
    {
        return $this->designation;
    }

    public function getPerfDeductionPercentAttribute(): float
    {
        return (float) ($this->performance_deduction_percent ?? 0);
    }

    public function getPerfDeductionAmountAttribute(): float
    {
        return (float) ($this->performance_deduction_amount ?? 0);
    }
}
