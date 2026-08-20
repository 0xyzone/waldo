<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSuspension extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'start_date',
        'end_date',
        'reason',
        'attachments',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'attachments' => 'array',
        ];
    }

    /**
     * Get the employee associated with the suspension record.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_code');
    }

    /**
     * Check if the suspension is currently active.
     */
    public function isCurrentlyActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $today = Carbon::today();
        $startDate = $this->start_date instanceof Carbon ? $this->start_date : Carbon::parse($this->start_date);
        $endDate = $this->end_date instanceof Carbon ? $this->end_date : Carbon::parse($this->end_date);

        return $today->betweenIncluded($startDate->startOfDay(), $endDate->endOfDay());
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saved(function (EmployeeSuspension $record): void {
            if ($record->employee_id && $record->isCurrentlyActive()) {
                $employee = Employee::where('employee_code', $record->employee_id)->first();
                if ($employee) {
                    $employee->update([
                        'employee_status' => 'Suspended',
                        'tips_status' => 'Hold',
                    ]);
                }
            }
        });
    }
}
