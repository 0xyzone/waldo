<?php

namespace App\Models;

use App\Jobs\SyncPromotionToSheetJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

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

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (EmployeePromotion $promotion): void {
            $employee = Employee::where('employee_code', $promotion->employee_id)->first();

            if (! $employee) {
                return;
            }

            $updates = [];

            if ($promotion->to_department_id !== null) {
                $updates['department_id'] = $promotion->to_department_id;
            }

            if ($promotion->to_designation_id !== null) {
                $updates['designation_id'] = $promotion->to_designation_id;
            }

            if (! empty($updates)) {
                $employee->update($updates);
            }

            // Dispatch background Google Sheets sync
            $userId = Auth::id();
            SyncPromotionToSheetJob::dispatch($promotion->id, $userId);
        });
    }
}
