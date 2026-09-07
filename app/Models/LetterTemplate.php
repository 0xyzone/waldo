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
        'different_first_page_margins',
        'first_page_margin_top',
        'first_page_margin_bottom',
        'first_page_margin_left',
        'first_page_margin_right',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'different_first_page_margins' => 'boolean',
        ];
    }
}
