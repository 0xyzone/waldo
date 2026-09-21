<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterGlobalVariable extends Model
{
    protected $fillable = [
        'key',
        'label',
        'type',
        'is_permanent',
        'default_value',
        'options',
        'formulas',
        'description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_permanent' => 'boolean',
            'formulas' => 'array',
        ];
    }
}
