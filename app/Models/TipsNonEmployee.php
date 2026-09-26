<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipsNonEmployee extends Model
{
    protected $fillable = [
        'code',
        'name',
        'designation',
        'tips_percentage',
        'distribution_amount',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tips_percentage' => 'decimal:2',
            'distribution_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Generate next auto-incrementing code with leading zeros (e.g., 001, 002).
     */
    public static function generateNextCode(): string
    {
        $maxNum = static::query()
            ->selectRaw('MAX(CAST(code AS UNSIGNED)) as max_code')
            ->value('max_code') ?? 0;

        return sprintf('%03d', (int) $maxNum + 1);
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->code)) {
                $model->code = static::generateNextCode();
            } elseif (is_numeric($model->code)) {
                $model->code = sprintf('%03d', (int) $model->code);
            }
        });

        static::updating(function ($model) {
            if (is_numeric($model->code)) {
                $model->code = sprintf('%03d', (int) $model->code);
            }
        });
    }
}
