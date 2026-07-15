<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Adjustment extends Model
{
    /**
     * Get the employee that owns the Adjustment
     */
    public function employee(): BelongsTo
    {
        // format: belongsTo(RelatedModel, foreign_key_on_this_table, owner_key_on_parent_table)
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_code');
    }
}
