<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class UnofficialNametagRequest extends Model
{
    protected $fillable = [
        'source',
        'employee_code',
        'employee_name',
        'name_on_tag',
        'remarks',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'created_by' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_code', 'employee_code');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actionLogs(): HasMany
    {
        return $this->hasMany(UnofficialNametagRequestLog::class, 'unofficial_nametag_request_id')->orderBy('created_at', 'desc');
    }

    protected static function booted(): void
    {
        static::creating(function (UnofficialNametagRequest $request) {
            if (empty($request->created_by) && Auth::check()) {
                $request->created_by = Auth::id();
            }

            if (empty($request->name_on_tag)) {
                $request->name_on_tag = $request->employee_name;
            }
        });

        static::created(function (UnofficialNametagRequest $request) {
            $user = Auth::user();
            $userName = $user ? ($user->name ?? $user->email) : 'System / Self';

            $request->actionLogs()->create([
                'user_id' => $user?->id,
                'user_name' => $userName,
                'from_status' => null,
                'to_status' => $request->status,
                'action_description' => "Request created with status '{$request->status}'",
                'created_at' => now(),
            ]);

            ActionLog::create([
                'actionable_type' => UnofficialNametagRequest::class,
                'actionable_id' => (string) $request->id,
                'user_id' => $user?->id,
                'user_name' => $userName,
                'action' => 'created',
                'from_status' => null,
                'to_status' => $request->status,
                'action_description' => "Request created with status '{$request->status}'",
                'changes' => null,
                'created_at' => now(),
            ]);
        });

        static::updating(function (UnofficialNametagRequest $request) {
            if ($request->isDirty('status')) {
                $user = Auth::user();
                $userName = $user ? ($user->name ?? $user->email) : 'System / Self';
                $fromStatus = $request->getOriginal('status');
                $toStatus = $request->status;

                $request->actionLogs()->create([
                    'user_id' => $user?->id,
                    'user_name' => $userName,
                    'from_status' => $fromStatus,
                    'to_status' => $toStatus,
                    'action_description' => "Status updated from '{$fromStatus}' to '{$toStatus}'",
                    'created_at' => now(),
                ]);

                ActionLog::create([
                    'actionable_type' => UnofficialNametagRequest::class,
                    'actionable_id' => (string) $request->id,
                    'user_id' => $user?->id,
                    'user_name' => $userName,
                    'action' => 'status_changed',
                    'from_status' => $fromStatus,
                    'to_status' => $toStatus,
                    'action_description' => "Status updated from '{$fromStatus}' to '{$toStatus}'",
                    'changes' => ['status' => ['old' => $fromStatus, 'new' => $toStatus]],
                    'created_at' => now(),
                ]);
            }
        });
    }
}
