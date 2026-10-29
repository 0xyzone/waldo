<?php

namespace App\Models;

use App\Traits\HasActionLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoyaltyReport extends Model
{
    use HasActionLogs;

    protected $fillable = [
        'title',
        'month',
        'year',
        'cutoff_date',
        'attendance_file_path',
        'performance_file_path',
        'status',
        'validated_at',
        'validated_by',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'cutoff_date' => 'date',
            'validated_at' => 'datetime',
            'year' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(RoyaltyReportItem::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isValidated(): bool
    {
        return $this->status === 'validated';
    }

    public function validateAndLock(int $userId): void
    {
        $this->update([
            'status' => 'validated',
            'validated_at' => now(),
            'validated_by' => $userId,
        ]);
    }

    public function unlock(): void
    {
        $this->update([
            'status' => 'generated',
            'validated_at' => null,
            'validated_by' => null,
        ]);
    }

    public function getTotalPayoutAttribute(): float
    {
        return (float) $this->items()->sum('final_payout');
    }

    public function getTotalDeductionsAttribute(): float
    {
        return (float) $this->items()->sum('total_deduction_amount');
    }
}
