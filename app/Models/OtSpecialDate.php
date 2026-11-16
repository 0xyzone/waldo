<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtSpecialDate extends Model
{
    protected $fillable = [
        'date',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
