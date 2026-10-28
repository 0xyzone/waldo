<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class IdCardRequest extends Model
{
    protected $fillable = [
        'source',
        'employee_code',
        'employee_name',
        'employee_designation',
        'employee_department',
        'notes',
        'status',
    ];

    public function actionLogs(): HasMany
    {
        return $this->hasMany(IdCardRequestLog::class, 'id_card_request_id')->orderBy('created_at', 'desc');
    }

    protected static function booted(): void
    {
        static::created(function (IdCardRequest $request) {
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
                'actionable_type' => IdCardRequest::class,
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

        static::updating(function (IdCardRequest $request) {
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
                    'actionable_type' => IdCardRequest::class,
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
