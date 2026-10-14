<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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
        'held_tips',
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
            'collection_summary' => 'array',
            'company_errors' => 'array',
            'left_outs' => 'array',
            'held_tips' => 'array',
        ];
    }

    public function getValidTillDateAttribute(): string
    {
        try {
            $month = $this->month ?: 'January';
            $year = $this->year ?: date('Y');

            return Carbon::parse("1 {$month} {$year}")
                ->addMonthNoOverflow()
                ->day(18)
                ->format('d F, Y');
        } catch (\Throwable) {
            return Carbon::parse($this->cutoff_date ?? now())
                ->addMonthNoOverflow()
                ->day(18)
                ->format('d F, Y');
        }
    }

    public function isValidated(): bool
    {
        return $this->status === 'validated';
    }

    /**
     * Validate and lock the report, updating applied tips adjustments to 'updated'.
     */
    public function validateAndLock(?int $userId = null): int
    {
        $month = strtolower((string) ($this->month ?? ''));
        $year = (string) ($this->year ?? '');
        $processedCodes = $this->items()
            ->pluck('employee_code')
            ->map(fn ($c) => strtoupper(trim((string) $c)))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $updatedCount = 0;
        if (! empty($processedCodes)) {
            $adjustmentsQuery = TipsAdjustment::query();
            if ($month !== '') {
                $adjustmentsQuery->whereRaw('LOWER(for_month) = ?', [$month]);
            }
            if ($year !== '') {
                $adjustmentsQuery->where('year', $year);
            }
            $updatedCount = $adjustmentsQuery
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', '!=', 'cancelled');
                })
                ->whereIn(DB::raw('UPPER(TRIM(employee_id))'), $processedCodes)
                ->update(['status' => 'updated']);
        }

        $this->update([
            'status' => 'validated',
            'validated_at' => now(),
            'validated_by' => $userId,
        ]);

        return $updatedCount;
    }

    /**
     * Unlock the report, reverting applied tips adjustments to 'pending'.
     */
    public function unlock(): int
    {
        $month = strtolower((string) ($this->month ?? ''));
        $year = (string) ($this->year ?? '');
        $processedCodes = $this->items()
            ->pluck('employee_code')
            ->map(fn ($c) => strtoupper(trim((string) $c)))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $revertedCount = 0;
        if (! empty($processedCodes)) {
            $adjustmentsQuery = TipsAdjustment::query();
            if ($month !== '') {
                $adjustmentsQuery->whereRaw('LOWER(for_month) = ?', [$month]);
            }
            if ($year !== '') {
                $adjustmentsQuery->where('year', $year);
            }
            $revertedCount = $adjustmentsQuery
                ->where('status', 'updated')
                ->whereIn(DB::raw('UPPER(TRIM(employee_id))'), $processedCodes)
                ->update(['status' => 'pending']);
        }

        $this->update([
            'status' => 'generated',
        ]);

        return $revertedCount;
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
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
