<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFont extends Model
{
    protected $fillable = [
        'family_name',
        'file_name',
        'original_name',
        'style',
        'weight',
    ];
}
