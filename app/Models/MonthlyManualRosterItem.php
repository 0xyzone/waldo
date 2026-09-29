<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyManualRosterItem extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'monthly_manual_roster_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'monthly_manual_roster_id',
        'employee_code',
        'employee_name',
        'department',
        'designation',
        'is_roster_updated',
        'updated_at_hrms',
        'updated_by',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_roster_updated' => 'boolean',
            'updated_at_hrms' => 'datetime',
        ];
    }

    /**
     * Get the monthly manual roster this item belongs to.
     */
    public function roster(): BelongsTo
    {
        return $this->belongsTo(MonthlyManualRoster::class, 'monthly_manual_roster_id');
    }

    /**
     * Get the employee associated with this item.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_code', 'employee_code');
    }

    /**
     * Get the user who marked this item as updated.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope query to order by numeric portion of employee code.
     */
    public function scopeOrderByNumericCode(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderByRaw("CAST(REGEXP_SUBSTR(employee_code, '[0-9]+') AS UNSIGNED) {$direction}");
    }
}
