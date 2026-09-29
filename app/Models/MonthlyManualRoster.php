<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonthlyManualRoster extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'monthly_manual_rosters';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'year',
        'month',
        'title',
        'total_employees',
        'completed_count',
        'status',
        'notes',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'total_employees' => 'integer',
            'completed_count' => 'integer',
        ];
    }

    /**
     * Get the checklist items for this roster.
     */
    public function items(): HasMany
    {
        return $this->hasMany(MonthlyManualRosterItem::class, 'monthly_manual_roster_id');
    }

    /**
     * Get the user who created this roster.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the month name (e.g., September).
     */
    public function getMonthNameAttribute(): string
    {
        return Carbon::create()->month((int) $this->month)->format('F');
    }

    /**
     * Get the formatted period string (e.g., September 2026).
     */
    public function getPeriodAttribute(): string
    {
        return "{$this->month_name} {$this->year}";
    }

    /**
     * Get count of pending items.
     */
    public function getPendingCountAttribute(): int
    {
        return max(0, (int) $this->total_employees - (int) $this->completed_count);
    }

    /**
     * Get percentage of completed items.
     */
    public function getCompletionPercentageAttribute(): int
    {
        if ($this->total_employees <= 0) {
            return 0;
        }

        return (int) round(($this->completed_count / $this->total_employees) * 100);
    }

    /**
     * Recalculate item totals and update status.
     */
    public function refreshStatistics(): void
    {
        $total = $this->items()->count();
        $completed = $this->items()->where('is_roster_updated', true)->count();

        $status = 'in_progress';
        if ($total === 0) {
            $status = 'empty';
        } elseif ($completed >= $total) {
            $status = 'completed';
        }

        $this->update([
            'total_employees' => $total,
            'completed_count' => $completed,
            'status' => $status,
        ]);
    }
}
