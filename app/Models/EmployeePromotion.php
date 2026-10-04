<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePromotion extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'from_department_id',
        'from_designation_id',
        'to_department_id',
        'to_designation_id',
        'promotion_date',
        'acknowledged',
        'acknowledged_at',
        'hrms_synced',
        'hrms_synced_at',
        'remarks',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'promotion_date' => 'date',
            'acknowledged' => 'boolean',
            'acknowledged_at' => 'datetime',
            'hrms_synced' => 'boolean',
            'hrms_synced_at' => 'datetime',
        ];
    }

    /**
     * Get the employee associated with this promotion.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_code');
    }

    /**
     * Get the department the employee was promoted from.
     */
    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    /**
     * Get the designation the employee was promoted from.
     */
    public function fromDesignation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'from_designation_id');
    }

    /**
     * Get the department the employee was promoted to.
     */
    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    /**
     * Get the designation the employee was promoted to.
     */
    public function toDesignation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'to_designation_id');
    }
}
