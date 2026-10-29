<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoyaltyHeldRelease extends Model
{
    protected $table = 'royalty_held_releases';

    protected $fillable = [
        'employee_code',
        'amount',
        'merit_points',
        'release_date',
        'notes',
        'released_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'release_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_code', 'employee_code');
    }

    public function releaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }
}
