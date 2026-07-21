<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'rank',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rank' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the designations under this department.
     */
    public function designations(): HasMany
    {
        return $this->hasMany(Designation::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function biometricAllotments(): HasMany
    {
        return $this->hasMany(BiometricAllotment::class);
    }

    public function adjustment(): HasMany
    {
        return $this->hasMany(Adjustment::class);
    }
}
