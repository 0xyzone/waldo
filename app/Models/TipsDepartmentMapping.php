<?php

namespace App\Models;

use App\Traits\HasActionLogs;
use Illuminate\Database\Eloquent\Model;

class TipsDepartmentMapping extends Model
{
    use HasActionLogs;

    protected $fillable = [
        'page_name',
        'department_ids',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'department_ids' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Resolve page name for given department ID or name.
     */
    public static function resolvePageName(?int $deptId, ?string $deptName): string
    {
        $mappings = static::where('is_active', true)->orderBy('sort_order')->get();

        foreach ($mappings as $mapping) {
            $ids = (array) ($mapping->department_ids ?? []);
            if ($deptId && in_array((string) $deptId, array_map('strval', $ids), true)) {
                return $mapping->page_name;
            }
            if ($deptName && in_array($deptName, $ids, true)) {
                return $mapping->page_name;
            }
        }

        return $deptName ?: 'Back Office';
    }

    /**
     * Get the resolved department names assigned to a given page name / category.
     *
     * @return array<int, string>
     */
    public static function getDepartmentNamesForPage(string $pageName): array
    {
        $mapping = static::where('is_active', true)
            ->whereRaw('LOWER(TRIM(page_name)) = ?', [strtolower(trim($pageName))])
            ->first();

        if (! $mapping || empty($mapping->department_ids)) {
            return [];
        }

        static $departmentMap = null;
        if ($departmentMap === null) {
            $departmentMap = Department::pluck('name', 'id')->toArray();
        }

        $names = [];
        foreach ((array) $mapping->department_ids as $id) {
            if (isset($departmentMap[$id])) {
                $names[] = $departmentMap[$id];
            } elseif (in_array($id, $departmentMap, true)) {
                $names[] = (string) $id;
            } elseif (is_string($id) && filled($id)) {
                $names[] = $id;
            }
        }

        return array_values(array_unique($names));
    }
}
