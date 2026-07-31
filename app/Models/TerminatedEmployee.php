<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TerminatedEmployee extends Model
{
    protected $fillable = [
        'employee_id',
        'last_working_date',
        'termination_date',
        'reason',
    ];

    /**
     * Get the employee associated with the termination record.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_code');
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (TerminatedEmployee $record): void {
            if ($record->employee_id) {
                $employee = Employee::where('employee_code', $record->employee_id)->first();
                if ($employee) {
                    $employee->update(['employee_status' => 'Terminated']);
                }
            }
        });
    }
}
