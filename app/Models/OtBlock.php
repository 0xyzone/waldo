<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OtBlock extends Model
{
    protected $fillable = [
        'name',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function overtimeEntries(): HasMany
    {
        return $this->hasMany(OvertimeEntry::class, 'ot_block_id');
    }
}
