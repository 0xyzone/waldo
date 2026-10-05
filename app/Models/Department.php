<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'parent_id',
        'name',
        'rank',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'rank' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the parent department.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    /**
     * Get the child departments.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    /**
     * Get the effective manager employee for this department (or from parent if not found).
     */
    public function getEffectiveManager(): ?Employee
    {
        $manager = Employee::where('department_id', $this->id)
            ->where('employee_status', 'Active')
            ->where('is_manager', true)
            ->first();

        if (! $manager && $this->parent_id && $this->parent) {
            return $this->parent->getEffectiveManager();
        }

        return $manager;
    }

    /**
     * Get the designations under this department.
     */
    public function designations(): HasMany
    {
        return $this->hasMany(Designation::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function biometricAllotments(): HasMany
    {
        return $this->hasMany(BiometricAllotment::class);
    }

    public function adjustment(): HasMany
    {
        return $this->hasMany(Adjustment::class);
    }

    /**
     * Get IDs for Gaming and Slot departments dynamically across environments.
     *
     * @return array<int>
     */
    public static function getGamingAndSlotDepartmentIds(): array
    {
        $ids = static::query()
            ->where(function ($query) {
                $query->whereRaw('LOWER(TRIM(name)) in (?, ?, ?)', ['gaming', 'slot', 'slots'])
                    ->orWhereRaw('LOWER(TRIM(name)) like ?', ['%gaming%'])
                    ->orWhereRaw('LOWER(TRIM(name)) like ?', ['%slot%']);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (! empty($ids)) {
            return $ids;
        }

        return [6, 7];
    }

    /**
     * Get primary Gaming department ID dynamically.
     */
    public static function getGamingDepartmentId(): ?int
    {
        $id = static::query()
            ->where(function ($query) {
                $query->whereRaw('LOWER(TRIM(name)) = ?', ['gaming'])
                    ->orWhereRaw('LOWER(TRIM(name)) like ?', ['%gaming%']);
            })
            ->value('id');

        return $id ? (int) $id : (static::getGamingAndSlotDepartmentIds()[0] ?? 6);
    }

    /**
     * Get primary Slot department ID dynamically.
     */
    public static function getSlotDepartmentId(): ?int
    {
        $id = static::query()
            ->where(function ($query) {
                $query->whereRaw('LOWER(TRIM(name)) = ?', ['slot'])
                    ->orWhereRaw('LOWER(TRIM(name)) like ?', ['%slot%']);
            })
            ->value('id');

        return $id ? (int) $id : (static::getGamingAndSlotDepartmentIds()[1] ?? 7);
    }

    /**
     * Get options array for departments allowed in the random EOM/GOM pool (excludes Gaming & Slot).
     *
     * @return array<int, string>
     */
    public static function getAllowedPoolDepartmentsOptions(): array
    {
        $reservedIds = static::getGamingAndSlotDepartmentIds();

        return static::query()
            ->whereNotIn('id', $reservedIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
}
