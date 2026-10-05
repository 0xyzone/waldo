<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class TipsYearlyEomGomReport extends Model
{
    protected $fillable = [
        'year',
        'title',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TipsYearlyEomGomReportEntry::class, 'report_id')
            ->orderBy('month_number')
            ->orderBy('entry_number');
    }

    /**
     * Get entries grouped by month number (1 to 12).
     */
    public function getEntriesByMonth(): Collection
    {
        return $this->entries->groupBy('month_number');
    }

    /**
     * Get evaluated period (month & year) and release label for a given month number (1 - 12).
     */
    public function getPeriodForMonth(int $monthNumber): array
    {
        $evaluatedMonths = [
            1 => 'December',
            2 => 'January',
            3 => 'February',
            4 => 'March',
            5 => 'April',
            6 => 'May',
            7 => 'June',
            8 => 'July',
            9 => 'August',
            10 => 'September',
            11 => 'October',
            12 => 'November',
        ];

        $releaseMonths = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];

        $evaluatedMonth = $evaluatedMonths[$monthNumber] ?? 'December';
        $evaluatedYear = $monthNumber === 1 ? ($this->year - 1) : $this->year;
        $releaseMonth = $releaseMonths[$monthNumber] ?? 'January';

        return [
            'month_number' => $monthNumber,
            'evaluated_month' => $evaluatedMonth,
            'evaluated_month_short' => substr($evaluatedMonth, 0, 3),
            'evaluated_year' => $evaluatedYear,
            'evaluated_year_short' => substr((string) $evaluatedYear, -2),
            'evaluated_label' => "{$evaluatedMonth} {$evaluatedYear}",
            'release_month' => $releaseMonth,
            'release_month_short' => substr($releaseMonth, 0, 3),
            'release_label' => "{$releaseMonth} Release",
            'tab_label' => substr($releaseMonth, 0, 3).' ('.substr($evaluatedMonth, 0, 3)." '".substr((string) $evaluatedYear, -2).')',
        ];
    }

    /**
     * Generate or initialize 12 months with 4 entries per month.
     */
    public function generateEntries(): void
    {
        // 1. Identify excluded departments
        // Excluded list from TipsEomGomExcludedDepartment + Gaming & Slot (dynamic IDs)
        $gamingSlotDepartmentIds = Department::getGamingAndSlotDepartmentIds();
        $excludedDepartmentIds = TipsEomGomExcludedDepartment::pluck('department_id')
            ->map(fn ($id) => (int) $id)
            ->merge($gamingSlotDepartmentIds)
            ->unique()
            ->values()
            ->all();

        // 2. Fetch allowed departments
        $allowedDepartments = Department::whereNotIn('id', $excludedDepartmentIds)
            ->where('is_active', true)
            ->get();

        // If somehow not enough allowed departments, fall back to any active departments excluding Gaming & Slot
        if ($allowedDepartments->count() < 3) {
            $allowedDepartments = Department::whereNotIn('id', $gamingSlotDepartmentIds)
                ->where('is_active', true)
                ->get();
        }

        for ($monthNumber = 1; $monthNumber <= 12; $monthNumber++) {
            $period = $this->getPeriodForMonth($monthNumber);
            $monthName = $period['evaluated_month'];
            $existingEntries = $this->entries()->where('month_number', $monthNumber)->get();

            // Random selection of 3 distinct departments from allowed departments
            $availablePool = $allowedDepartments->shuffle();
            $deptEntry2 = $availablePool->first();
            $deptEntry3 = $availablePool->skip(1)->first() ?? $deptEntry2;
            $deptEntry4 = $availablePool->skip(2)->first() ?? $deptEntry2;

            // Entry 1: Gaming / Slot (2 EOM, 1 GOM)
            $entry1 = $existingEntries->firstWhere('entry_number', 1);
            if (! $entry1) {
                $this->entries()->create([
                    'month_number' => $monthNumber,
                    'month_name' => $monthName,
                    'entry_number' => 1,
                    'is_gaming_slot' => true,
                    'department_id' => Department::getGamingDepartmentId(),
                    'department_name' => 'Gaming / Slot',
                ]);
            }

            // Entry 2: Random allowed department (1 EOM)
            $entry2 = $existingEntries->firstWhere('entry_number', 2);
            if (! $entry2) {
                $this->entries()->create([
                    'month_number' => $monthNumber,
                    'month_name' => $monthName,
                    'entry_number' => 2,
                    'is_gaming_slot' => false,
                    'department_id' => $deptEntry2?->id,
                    'department_name' => $deptEntry2?->name,
                ]);
            }

            // Entry 3: Random allowed department (1 GOM)
            $entry3 = $existingEntries->firstWhere('entry_number', 3);
            if (! $entry3) {
                $this->entries()->create([
                    'month_number' => $monthNumber,
                    'month_name' => $monthName,
                    'entry_number' => 3,
                    'is_gaming_slot' => false,
                    'department_id' => $deptEntry3?->id,
                    'department_name' => $deptEntry3?->name,
                ]);
            }

            // Entry 4: Random allowed department (1 EOM)
            $entry4 = $existingEntries->firstWhere('entry_number', 4);
            if (! $entry4) {
                $this->entries()->create([
                    'month_number' => $monthNumber,
                    'month_name' => $monthName,
                    'entry_number' => 4,
                    'is_gaming_slot' => false,
                    'department_id' => $deptEntry4?->id,
                    'department_name' => $deptEntry4?->name,
                ]);
            }
        }
    }

    /**
     * Check if a specific month is validated.
     */
    public function isMonthValidated(int $monthNumber): bool
    {
        return $this->entries()
            ->where('month_number', $monthNumber)
            ->where('is_validated', true)
            ->exists();
    }

    /**
     * Validate a specific month's winners.
     */
    public function validateMonth(int $monthNumber, ?int $userId = null): void
    {
        $this->entries()
            ->where('month_number', $monthNumber)
            ->update([
                'is_validated' => true,
                'validated_at' => now(),
                'validated_by' => $userId ?? auth()->id(),
            ]);
    }

    /**
     * Unlock / unvalidate a specific month.
     */
    public function unvalidateMonth(int $monthNumber): void
    {
        $this->entries()
            ->where('month_number', $monthNumber)
            ->update([
                'is_validated' => false,
                'validated_at' => null,
                'validated_by' => null,
            ]);
    }

    /**
     * Re-randomize entry 2, 3, and 4 departments for a specific month.
     * Automatically skips validated months.
     */
    public function rerandomizeMonth(int $monthNumber): bool
    {
        // Skip if the month has been validated
        if ($this->isMonthValidated($monthNumber)) {
            return false;
        }

        $gamingSlotDepartmentIds = Department::getGamingAndSlotDepartmentIds();
        $excludedDepartmentIds = TipsEomGomExcludedDepartment::pluck('department_id')
            ->map(fn ($id) => (int) $id)
            ->merge($gamingSlotDepartmentIds)
            ->unique()
            ->values()
            ->all();

        $allowedDepartments = Department::whereNotIn('id', $excludedDepartmentIds)
            ->where('is_active', true)
            ->get();

        if ($allowedDepartments->count() < 3) {
            $allowedDepartments = Department::whereNotIn('id', $gamingSlotDepartmentIds)
                ->where('is_active', true)
                ->get();
        }

        $availablePool = $allowedDepartments->shuffle();
        $deptEntry2 = $availablePool->first();
        $deptEntry3 = $availablePool->skip(1)->first() ?? $deptEntry2;
        $deptEntry4 = $availablePool->skip(2)->first() ?? $deptEntry2;

        $entry2 = $this->entries()->where('month_number', $monthNumber)->where('entry_number', 2)->first();
        if ($entry2) {
            $entry2->update([
                'department_id' => $deptEntry2?->id,
                'department_name' => $deptEntry2?->name,
            ]);
        }

        $entry3 = $this->entries()->where('month_number', $monthNumber)->where('entry_number', 3)->first();
        if ($entry3) {
            $entry3->update([
                'department_id' => $deptEntry3?->id,
                'department_name' => $deptEntry3?->name,
            ]);
        }

        $entry4 = $this->entries()->where('month_number', $monthNumber)->where('entry_number', 4)->first();
        if ($entry4) {
            $entry4->update([
                'department_id' => $deptEntry4?->id,
                'department_name' => $deptEntry4?->name,
            ]);
        }

        return true;
    }

    /**
     * Re-randomize all unvalidated months in the report.
     * Automatically skips any month that has been validated.
     */
    public function rerandomizeAll(): int
    {
        $updatedCount = 0;

        for ($month = 1; $month <= 12; $month++) {
            if ($this->rerandomizeMonth($month)) {
                $updatedCount++;
            }
        }

        return $updatedCount;
    }
}
