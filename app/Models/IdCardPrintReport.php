<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IdCardPrintReport extends Model
{
    protected $fillable = [
        'title',
        'batch_date',
        'csv_file_path',
        'total_records',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'batch_date' => 'date',
            'total_records' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(IdCardPrintReportItem::class);
    }

    public function getSentForPrintCountAttribute(): int
    {
        return $this->items()->where('status', 'sent for print')->count();
    }

    public function getPrintedCountAttribute(): int
    {
        return $this->items()->where('status', 'printed')->count();
    }

    public function getInOfficeCountAttribute(): int
    {
        return $this->items()->where('status', 'in office')->count();
    }

    public function getReleasedCountAttribute(): int
    {
        return $this->items()->where('status', 'released')->count();
    }

    public function getOnHoldCountAttribute(): int
    {
        return $this->items()->where('status', 'on hold')->count();
    }
}
