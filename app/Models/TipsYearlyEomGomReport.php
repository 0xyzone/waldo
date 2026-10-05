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
     * Generate or initialize 12 months with 3 entries per month.
     */
    public function generateEntries(): void
    {
        // 1. Identify excluded departments
        // Excluded list from TipsEomGomExcludedDepartment + Gaming (6) + Slot (7)
        $excludedDepartmentIds = TipsEomGomExcludedDepartment::pluck('department_id')
            ->map(fn ($id) => (int) $id)
            ->push(6, 7) // Automatically exclude Gaming & Slot
            ->unique()
            ->values()
            ->all();

        // 2. Fetch allowed departments
        $allowedDepartments = Department::whereNotIn('id', $excludedDepartmentIds)
            ->where('is_active', true)
            ->get();

        // If somehow no allowed departments, fall back to any active departments excluding 6 and 7
        if ($allowedDepartments->count() < 2) {
            $allowedDepartments = Department::whereNotIn('id', [6, 7])
                ->where('is_active', true)
                ->get();
        }

        $months = [
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

        foreach ($months as $monthNumber => $monthName) {
            $existingEntries = $this->entries()->where('month_number', $monthNumber)->get();

            // Random selection of 2 distinct departments from allowed departments
            $availablePool = $allowedDepartments->shuffle();
            $deptEntry2 = $availablePool->first();
            $deptEntry3 = $availablePool->skip(1)->first() ?? $deptEntry2;

            // Entry 1: Gaming / Slot (2 EOM, 1 GOM)
            $entry1 = $existingEntries->firstWhere('entry_number', 1);
            if (! $entry1) {
                $this->entries()->create([
                    'month_number' => $monthNumber,
                    'month_name' => $monthName,
                    'entry_number' => 1,
                    'is_gaming_slot' => true,
                    'department_id' => 6, // Default Gaming ID
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
        }
    }

    /**
     * Re-randomize entry 2 and 3 departments for a specific month.
     */
    public function rerandomizeMonth(int $monthNumber): void
    {
        $excludedDepartmentIds = TipsEomGomExcludedDepartment::pluck('department_id')
            ->map(fn ($id) => (int) $id)
            ->push(6, 7)
            ->unique()
            ->values()
            ->all();

        $allowedDepartments = Department::whereNotIn('id', $excludedDepartmentIds)
            ->where('is_active', true)
            ->get();

        if ($allowedDepartments->count() < 2) {
            $allowedDepartments = Department::whereNotIn('id', [6, 7])
                ->where('is_active', true)
                ->get();
        }

        $availablePool = $allowedDepartments->shuffle();
        $deptEntry2 = $availablePool->first();
        $deptEntry3 = $availablePool->skip(1)->first() ?? $deptEntry2;

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
    }
}
