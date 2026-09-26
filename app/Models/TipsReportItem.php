<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TipsReportItem extends Model
{
    protected $fillable = [
        'tips_report_id',
        'employee_id',
        'employee_code',
        'department',
        'department_rank',
        'designation',
        'designation_rank',
        'employee_name',
        'working_days',
        'present_days',
        'absent_days',
        'total_leaves',
        'late_in_count',
        'early_out_count',
        'join_date',
        'working_duration',
        'completion_factor',
        'point_value',
        'base_tips_amount',
        'tips_percentage',
        'is_blank',
        'is_fixed',
        'publish_tips',
        'tips_status',
        'amount_to_adjust',
        'amount_to_deduct',
        'percentage_to_deduct',
        'calculated_tips',
        'unrounded_amount',
        'final_distribution_amount',
        'is_left_out',
    ];

    protected function casts(): array
    {
        return [
            'department_rank' => 'integer',
            'designation_rank' => 'integer',
            'working_days' => 'decimal:2',
            'present_days' => 'decimal:2',
            'absent_days' => 'decimal:2',
            'total_leaves' => 'decimal:2',
            'late_in_count' => 'integer',
            'early_out_count' => 'integer',
            'completion_factor' => 'decimal:2',
            'point_value' => 'decimal:4',
            'base_tips_amount' => 'decimal:2',
            'tips_percentage' => 'decimal:2',
            'is_blank' => 'boolean',
            'is_fixed' => 'boolean',
            'publish_tips' => 'boolean',
            'amount_to_adjust' => 'decimal:2',
            'amount_to_deduct' => 'decimal:2',
            'percentage_to_deduct' => 'decimal:2',
            'calculated_tips' => 'decimal:2',
            'unrounded_amount' => 'decimal:2',
            'final_distribution_amount' => 'decimal:2',
            'is_left_out' => 'boolean',
        ];
    }

    public function tipsReport(): BelongsTo
    {
        return $this->belongsTo(TipsReport::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_code', 'employee_code');
    }
}
