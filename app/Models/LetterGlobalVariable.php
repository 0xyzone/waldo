<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterGlobalVariable extends Model
{
    protected $fillable = [
        'key',
        'label',
        'type',
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
            'formulas' => 'array',
        ];
    }
}
