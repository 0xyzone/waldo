<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Designation extends Model
{
    use SoftDeletes;

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
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'rank' => 'integer',
        'is_active' => 'boolean',
    ];

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
