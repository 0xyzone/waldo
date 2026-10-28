<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class ActionLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'actionable_type',
        'actionable_id',
        'user_id',
        'user_name',
        'action',
        'from_status',
        'to_status',
        'action_description',
        'changes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'changes' => 'array',
            'user_id' => 'integer',
        ];
    }

    public function actionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getResourceNameAttribute(): string
    {
        $class = class_basename($this->actionable_type);

        return Str::headline(Str::snake($class));
    }

    public function getRecordTitleAttribute(): string
    {
        try {
            $record = $this->actionable;
            if (! $record) {
                return "#{$this->actionable_id}";
            }

            if (isset($record->name)) {
                return (string) $record->name;
            }

            if (isset($record->title)) {
                return (string) $record->title;
            }

            if (isset($record->employee_name)) {
                return (string) $record->employee_name;
            }

            if (isset($record->code)) {
                return (string) $record->code;
            }

            if (isset($record->employee_code)) {
                return (string) $record->employee_code;
            }

            if (isset($record->month) && isset($record->year)) {
                return "{$record->month} {$record->year}";
            }

            return "#{$this->actionable_id}";
        } catch (\Throwable) {
            return "#{$this->actionable_id}";
        }
    }
}
