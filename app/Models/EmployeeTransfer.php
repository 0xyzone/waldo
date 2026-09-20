<?php

namespace App\Models;

use App\Jobs\SyncTransferToSheetJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class EmployeeTransfer extends Model
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
        'transfer_date',
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
            'transfer_date' => 'date',
            'acknowledged' => 'boolean',
            'acknowledged_at' => 'datetime',
            'hrms_synced' => 'boolean',
            'hrms_synced_at' => 'datetime',
        ];
    }

    /**
     * Get the employee associated with this transfer.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_code');
    }

    /**
     * Get the department the employee was transferred from.
     */
    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    /**
     * Get the designation the employee was transferred from.
     */
    public function fromDesignation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'from_designation_id');
    }

    /**
     * Get the department the employee was transferred to.
     */
    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    /**
     * Get the designation the employee was transferred to.
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
        static::created(function (EmployeeTransfer $transfer): void {
            $today = now()->toDateString();
            $transferDate = $transfer->transfer_date?->toDateString();

            // Only apply if transfer date is today or in the past
            if ($transferDate && $transferDate <= $today) {
                $employee = Employee::where('employee_code', $transfer->employee_id)->first();

                if ($employee) {
                    $updates = [];

                    if ($transfer->to_department_id !== null) {
                        $updates['department_id'] = $transfer->to_department_id;
                    }

                    if ($transfer->to_designation_id !== null) {
                        $updates['designation_id'] = $transfer->to_designation_id;
                    }

                    if (! empty($updates)) {
                        $employee->update($updates);
                    }
                }

                $transfer->updateQuietly([
                    'hrms_synced' => true,
                    'hrms_synced_at' => now(),
                ]);

                // Dispatch background Google Sheets sync
                $userId = Auth::id();
                SyncTransferToSheetJob::dispatch($transfer->id, $userId);
            }
        });
    }
}
