<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LetterTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ActionLogValueResolver
{
    /**
     * In-memory cache for resolved entities per request: [entity_type => [id => label]]
     *
     * @var array<string, array<string|int, string>>
     */
    protected static array $cache = [];

    /**
     * Resolve a field's value to a human-readable representation.
     *
     * @return array{label: string, original: mixed, is_resolved: bool}
     */
    public static function resolve(string $field, mixed $value, ?string $actionableType = null): array
    {
        if (is_null($value) || $value === '') {
            return [
                'label' => 'null',
                'original' => $value,
                'is_resolved' => false,
            ];
        }

        if (is_bool($value)) {
            return [
                'label' => $value ? 'true' : 'false',
                'original' => $value,
                'is_resolved' => false,
            ];
        }

        $fieldLower = strtolower($field);

        // 1. Designation fields
        if (in_array($fieldLower, ['designation_id', 'from_designation_id', 'to_designation_id', 'new_designation_id', 'old_designation_id'], true)) {
            $name = self::lookup(Designation::class, $value, function ($id) {
                return Designation::withTrashed()->find($id)?->name;
            });

            if ($name) {
                return [
                    'label' => $name,
                    'original' => $value,
                    'is_resolved' => true,
                ];
            }
        }

        // 2. Department fields
        if (in_array($fieldLower, ['department_id', 'from_department_id', 'to_department_id', 'new_department_id', 'old_department_id'], true)) {
            $name = self::lookup(Department::class, $value, function ($id) {
                return Department::withTrashed()->find($id)?->name;
            });

            if ($name) {
                return [
                    'label' => $name,
                    'original' => $value,
                    'is_resolved' => true,
                ];
            }
        }

        // 3. Employee fields (employee_id, employee_code, emp_id)
        if (in_array($fieldLower, ['employee_id', 'employee_code', 'emp_id'], true)) {
            $emp = self::lookup(Employee::class, $value, function ($id) {
                $e = Employee::find($id);

                return $e ? "{$e->name} ({$e->employee_code})" : null;
            });

            if ($emp) {
                return [
                    'label' => $emp,
                    'original' => $value,
                    'is_resolved' => true,
                ];
            }
        }

        // 4. User fields (user_id, approved_by, created_by, updated_by, reviewed_by, assigned_to)
        if (in_array($fieldLower, ['user_id', 'approved_by', 'created_by', 'updated_by', 'reviewed_by', 'assigned_to'], true)) {
            $user = self::lookup(User::class, $value, function ($id) {
                return User::find($id)?->name;
            });

            if ($user) {
                return [
                    'label' => $user,
                    'original' => $value,
                    'is_resolved' => true,
                ];
            }
        }

        // 5. Candidate fields
        if ($fieldLower === 'candidate_id') {
            $name = self::lookup(Candidate::class, $value, function ($id) {
                return Candidate::find($id)?->name;
            });

            if ($name) {
                return [
                    'label' => $name,
                    'original' => $value,
                    'is_resolved' => true,
                ];
            }
        }

        // 6. Letter Template fields
        if ($fieldLower === 'letter_template_id') {
            $title = self::lookup(LetterTemplate::class, $value, function ($id) {
                return LetterTemplate::find($id)?->title;
            });

            if ($title) {
                return [
                    'label' => $title,
                    'original' => $value,
                    'is_resolved' => true,
                ];
            }
        }

        // 7. Generic relationship lookup if actionableType is available and field ends with _id
        if ($actionableType && str_ends_with($fieldLower, '_id') && class_exists($actionableType)) {
            $relationName = Str::camel(Str::beforeLast($field, '_id'));

            try {
                $instance = new $actionableType;
                if (method_exists($instance, $relationName)) {
                    $relation = $instance->{$relationName}();
                    if ($relation instanceof BelongsTo) {
                        $relatedClass = get_class($relation->getRelated());
                        $name = self::lookup($relatedClass, $value, function ($id) use ($relatedClass) {
                            $item = $relatedClass::find($id);

                            return $item?->name ?? $item?->title ?? $item?->label ?? null;
                        });

                        if ($name) {
                            return [
                                'label' => $name,
                                'original' => $value,
                                'is_resolved' => true,
                            ];
                        }
                    }
                }
            } catch (\Throwable) {
                // Silently fallback to raw value
            }
        }

        return [
            'label' => is_scalar($value) ? (string) $value : json_encode($value),
            'original' => $value,
            'is_resolved' => false,
        ];
    }

    /**
     * Format a database column name into a clean, human-readable label.
     * E.g. 'designation_id' -> 'Designation', 'from_department_id' -> 'From Department'.
     */
    public static function formatFieldLabel(string $field): string
    {
        $fieldLower = strtolower($field);

        if (str_ends_with($fieldLower, '_id')) {
            $withoutId = substr($field, 0, -3);

            return Str::headline(str_replace('_', ' ', $withoutId));
        }

        return Str::headline(str_replace('_', ' ', $field));
    }

    /**
     * Cache-backed entity lookup.
     */
    protected static function lookup(string $class, mixed $id, callable $resolver): ?string
    {
        if (is_null($id) || $id === '') {
            return null;
        }

        $key = (string) $id;

        if (isset(self::$cache[$class][$key])) {
            return self::$cache[$class][$key];
        }

        try {
            $result = $resolver($id);
            if ($result) {
                self::$cache[$class][$key] = (string) $result;

                return self::$cache[$class][$key];
            }
        } catch (\Throwable) {
            // Silently return null on query error
        }

        return null;
    }
}
