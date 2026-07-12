<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterTemplate extends Model
{
    protected $fillable = [
        'title',
        'content',
        'variables',
        'margin_top',
        'margin_bottom',
        'margin_left',
        'margin_right',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
        ];
    }
}
