<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipsReport extends Model
{
    protected $fillable = [
        'title',
        'month',
        'year',
        'cutoff_date',
        'excel_file_path',
        'collection_summary',
        'company_errors',
        'left_outs',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'cutoff_date' => 'date',
            'collection_summary' => 'array',
            'company_errors' => 'array',
            'left_outs' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TipsReportItem::class);
    }
}
