<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leaver extends Model
{
    /**
     * Get the employee that is leaving.
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
        static::saved(function (Leaver $leaver): void {
            if ($leaver->employee_id) {
                $employee = Employee::where('employee_code', $leaver->employee_id)->first();
                if ($employee && $employee->employee_status !== 'Resigning This Month') {
                    $employee->update(['employee_status' => 'Resigning This Month']);
                }
            }
        });
    }
}
