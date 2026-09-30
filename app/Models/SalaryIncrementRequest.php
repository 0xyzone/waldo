<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryIncrementRequest extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'request_number',
        'date_requested',
        'date_approved',
        'date_applicable',
        'department_id',
        'hod_id',
        'employee_id',
        'current_designation_id',
        'proposed_designation_id',
        'current_salary',
        'proposed_salary',
        'increment_amount',
        'increment_percentage',
        'reason',
        'notes',
        'status',
        'hr_acknowledged',
        'hr_acknowledged_by',
        'hr_acknowledged_at',
        'hr_notes',
        'finance_acknowledged',
        'finance_acknowledged_by',
        'finance_acknowledged_at',
        'finance_notes',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_requested' => 'date',
            'date_approved' => 'date',
            'date_applicable' => 'date',
            'current_salary' => 'decimal:2',
            'proposed_salary' => 'decimal:2',
            'increment_amount' => 'decimal:2',
            'increment_percentage' => 'decimal:2',
            'hr_acknowledged' => 'boolean',
            'hr_acknowledged_at' => 'datetime',
            'finance_acknowledged' => 'boolean',
            'finance_acknowledged_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->request_number)) {
                $year = now()->format('Y');
                $count = static::withTrashed()->whereYear('created_at', $year)->count() + 1;
                $model->request_number = 'SIR-'.$year.'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Get the employee for whom the increment is requested.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_code');
    }

    /**
     * Get the Head of Department recommending the increment.
     */
    public function hod(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'hod_id', 'employee_code');
    }

    /**
     * Get the department of the employee.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Get the current designation of the employee.
     */
    public function currentDesignation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'current_designation_id');
    }

    /**
     * Get the proposed designation of the employee (if promoted).
     */
    public function proposedDesignation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'proposed_designation_id');
    }

    /**
     * Get the user who HR acknowledged this request.
     */
    public function hrAcknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_acknowledged_by');
    }

    /**
     * Get the user who Finance acknowledged this request.
     */
    public function financeAcknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finance_acknowledged_by');
    }

    /**
     * Get the user who created this request record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
