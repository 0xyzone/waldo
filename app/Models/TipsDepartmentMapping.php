<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipsDepartmentMapping extends Model
{
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
}
