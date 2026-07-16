<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
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
