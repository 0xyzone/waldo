<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Designation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'department_id',
        'name',
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
}
