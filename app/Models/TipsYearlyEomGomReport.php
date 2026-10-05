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
        for ($monthNumber = 1; $monthNumber <= 12; $monthNumber++) {
            $period = $this->getPeriodForMonth($monthNumber);
            $monthName = $period['evaluated_month'];
            $existingEntries = $this->entries()->where('month_number', $monthNumber)->get();

            // Random selection of 3 distinct allowed departments avoiding repetition for at least 4 months
            $chosenDepts = $this->selectRandomAllowedDepartments($monthNumber, 3, ignoreFutureUnvalidated: true);
            $deptEntry2 = $chosenDepts->get(0);
            $deptEntry3 = $chosenDepts->get(1) ?? $deptEntry2;
            $deptEntry4 = $chosenDepts->get(2) ?? $deptEntry2;

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
     * Select distinct allowed departments for randomized entries (Entry 2, 3, 4) in a given month.
     * Tries not to repeat any department for at least 4 months whenever possible.
     *
     * @return Collection<int, Department>
     */
    public function selectRandomAllowedDepartments(int $monthNumber, int $count = 3, bool $ignoreFutureUnvalidated = false): Collection
    {
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

        if ($allowedDepartments->count() < $count) {
            $allowedDepartments = Department::whereNotIn('id', $gamingSlotDepartmentIds)
                ->where('is_active', true)
                ->get();
        }

        if ($allowedDepartments->isEmpty()) {
            return collect();
        }

        // Query existing entries in this report excluding current month
        $query = $this->entries()
            ->where('month_number', '!=', $monthNumber)
            ->where('is_gaming_slot', false)
            ->whereNotNull('department_id');

        if ($ignoreFutureUnvalidated) {
            $query->where(function ($q) use ($monthNumber) {
                $q->where('month_number', '<', $monthNumber)
                    ->orWhere('is_validated', true);
            });
        }

        $otherEntries = $query->get();

        // Also check previous year's report if this is an early month (months 1 - 4)
        $prevYearEntries = collect();
        if ($monthNumber <= 4) {
            $prevYearReport = static::where('year', $this->year - 1)->first();
            if ($prevYearReport) {
                $prevYearEntries = $prevYearReport->entries()
                    ->where('is_gaming_slot', false)
                    ->whereNotNull('department_id')
                    ->where('month_number', '>=', 12 - (4 - $monthNumber))
                    ->get();
            }
        }

        $scored = $allowedDepartments->map(function (Department $dept) use ($monthNumber, $otherEntries, $prevYearEntries) {
            $minDist = 999;
            $occurrences = 0;

            foreach ($otherEntries as $entry) {
                if ($entry->department_id === $dept->id) {
                    $occurrences++;
                    $dist = abs($monthNumber - (int) $entry->month_number);
                    if ($dist < $minDist) {
                        $minDist = $dist;
                    }
                }
            }

            foreach ($prevYearEntries as $entry) {
                if ($entry->department_id === $dept->id) {
                    $dist = (12 - (int) $entry->month_number) + $monthNumber;
                    if ($dist < $minDist) {
                        $minDist = $dist;
                    }
                }
            }

            return [
                'dept' => $dept,
                'min_dist' => $minDist,
                'occurrences' => $occurrences,
                'is_cooldown' => $minDist <= 4,
                'rand' => mt_rand(1, 100000),
            ];
        });

        // Sorting priority:
        // 1. Not in cooldown (outside 4-month window)
        // 2. Fewest occurrences in current report
        // 3. Greatest min distance
        // 4. Random shuffle
        $sorted = $scored->sort(function (array $a, array $b) {
            if ($a['is_cooldown'] !== $b['is_cooldown']) {
                return $a['is_cooldown'] ? 1 : -1;
            }
            if ($a['occurrences'] !== $b['occurrences']) {
                return $a['occurrences'] <=> $b['occurrences'];
            }
            if ($a['min_dist'] !== $b['min_dist']) {
                return $b['min_dist'] <=> $a['min_dist'];
            }

            return $a['rand'] <=> $b['rand'];
        })->values();

        return $sorted->take($count)->pluck('dept');
    }

    /**
     * Re-randomize entry 2, 3, and 4 departments for a specific month.
     * Automatically skips validated months.
     */
    public function rerandomizeMonth(int $monthNumber, bool $ignoreFutureUnvalidated = false): bool
    {
        // Skip if the month has been validated
        if ($this->isMonthValidated($monthNumber)) {
            return false;
        }

        $chosenDepts = $this->selectRandomAllowedDepartments($monthNumber, 3, $ignoreFutureUnvalidated);
        $deptEntry2 = $chosenDepts->get(0);
        $deptEntry3 = $chosenDepts->get(1) ?? $deptEntry2;
        $deptEntry4 = $chosenDepts->get(2) ?? $deptEntry2;

        $entry2 = $this->entries()->where('month_number', $monthNumber)->where('entry_number', 2)->first();
        if ($entry2) {
            $data2 = [
                'department_id' => $deptEntry2?->id,
                'department_name' => $deptEntry2?->name,
            ];
            if ($entry2->department_id !== $deptEntry2?->id) {
                $data2['eom_employee_code_1'] = null;
                $data2['eom_remarks_1'] = null;
            }
            $entry2->update($data2);
        }

        $entry3 = $this->entries()->where('month_number', $monthNumber)->where('entry_number', 3)->first();
        if ($entry3) {
            $data3 = [
                'department_id' => $deptEntry3?->id,
                'department_name' => $deptEntry3?->name,
            ];
            if ($entry3->department_id !== $deptEntry3?->id) {
                $data3['gom_employee_code_1'] = null;
                $data3['gom_remarks_1'] = null;
            }
            $entry3->update($data3);
        }

        $entry4 = $this->entries()->where('month_number', $monthNumber)->where('entry_number', 4)->first();
        if ($entry4) {
            $data4 = [
                'department_id' => $deptEntry4?->id,
                'department_name' => $deptEntry4?->name,
            ];
            if ($entry4->department_id !== $deptEntry4?->id) {
                $data4['eom_employee_code_1'] = null;
                $data4['eom_remarks_1'] = null;
            }
            $entry4->update($data4);
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
            if ($this->rerandomizeMonth($month, ignoreFutureUnvalidated: true)) {
                $updatedCount++;
            }
        }

        return $updatedCount;
    }
}
