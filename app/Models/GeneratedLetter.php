<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratedLetter extends Model
{
    protected $fillable = [
        'letter_template_id',
        'employee_code',
        'template_title',
        'employee_name',
        'content',
        'custom_values',
        'margins',
    ];

    protected function casts(): array
    {
        return [
            'custom_values' => 'array',
            'margins' => 'array',
        ];
    }

    public function template()
    {
        return $this->belongsTo(LetterTemplate::class, 'letter_template_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_code', 'employee_code');
    }
}
