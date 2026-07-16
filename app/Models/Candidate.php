<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Candidate extends Model
{
    /**
     * Get the department that owns the Candidate
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
