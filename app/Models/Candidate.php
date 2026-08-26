<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Candidate extends Model
{
    protected $fillable = [
        'cv_image',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cv_image' => 'array',
        ];
    }

    /**
     * Get the department that owns the Candidate
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
