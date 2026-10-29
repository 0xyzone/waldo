<?php

namespace App\Models;

use App\Traits\HasActionLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Designation extends Model
{
    use HasActionLogs, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'department_id',
        'name',
        'job_description',
        'rank',
        'default_tips_amount',
        'royalty_annual_budget',
        'royalty_monthly_max',
        'royalty_monthly_min',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'rank' => 'integer',
        'default_tips_amount' => 'decimal:2',
        'royalty_annual_budget' => 'decimal:2',
        'royalty_monthly_max' => 'decimal:2',
        'royalty_monthly_min' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $designation) {
            $budget = (float) ($designation->royalty_annual_budget ?? 0);
            if ($budget > 0) {
                $monthlyRaw = $budget / 12;
                $designation->royalty_monthly_max = ceil($monthlyRaw / 100) * 100;
                $designation->royalty_monthly_min = round($designation->royalty_monthly_max / 2, 2);
            } else {
                $designation->royalty_monthly_max = 0;
                $designation->royalty_monthly_min = 0;
            }
        });
    }

    /**
     * Get the department that owns the designation.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the employees associated with the designation.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'designation_id', 'id');
    }

    /**
     * Scope a query to only include active designations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order designations by rank.
     */
    public function scopeOrdered($query)
    {
        return $query
            ->orderByRaw('CASE WHEN `designations`.`rank` IS NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('rank')
            ->orderBy('name');
    }
}
