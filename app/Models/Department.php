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
}
