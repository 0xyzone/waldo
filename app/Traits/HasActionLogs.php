<?php

namespace App\Traits;

use App\Models\ActionLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait HasActionLogs
{
    public function actionLogs(): MorphMany
    {
        return $this->morphMany(ActionLog::class, 'actionable')->orderBy('created_at', 'desc');
    }

    public static function bootHasActionLogs(): void
    {
        static::created(function (Model $model): void {
            $user = Auth::user();
            $userName = $user ? ($user->name ?? $user->email) : 'System / Auto';
            $status = $model->getAttribute('status');

            $description = 'Record created'.($status ? " with status '{$status}'" : '');

            ActionLog::create([
                'actionable_type' => $model->getMorphClass(),
                'actionable_id' => (string) $model->getKey(),
                'user_id' => $user?->id,
                'user_name' => $userName,
                'action' => 'created',
                'from_status' => null,
                'to_status' => $status ? (string) $status : null,
                'action_description' => $description,
                'changes' => null,
                'created_at' => now(),
            ]);
        });

        static::updating(function (Model $model): void {
            $dirty = $model->getDirty();
            $ignored = ['updated_at', 'created_at', 'remember_token', 'password', 'two_factor_secret', 'two_factor_recovery_codes'];

            $changes = [];
            foreach ($dirty as $key => $newValue) {
                if (in_array($key, $ignored, true)) {
                    continue;
                }

                $oldValue = $model->getOriginal($key);

                // Skip if effectively identical
                if ($oldValue === $newValue || (is_numeric($oldValue) && is_numeric($newValue) && (string) $oldValue === (string) $newValue)) {
                    continue;
                }

                $changes[$key] = [
                    'old' => is_scalar($oldValue) || is_null($oldValue) ? $oldValue : json_encode($oldValue),
                    'new' => is_scalar($newValue) || is_null($newValue) ? $newValue : json_encode($newValue),
                ];
            }

            if (empty($changes)) {
                return;
            }

            $user = Auth::user();
            $userName = $user ? ($user->name ?? $user->email) : 'System / Auto';

            $isStatusChanged = array_key_exists('status', $changes);
            $fromStatus = $isStatusChanged ? (string) ($changes['status']['old'] ?? '') : null;
            $toStatus = $isStatusChanged ? (string) ($changes['status']['new'] ?? '') : null;
            $action = $isStatusChanged ? 'status_changed' : 'updated';

            if ($isStatusChanged) {
                $actionDescription = "Status updated from '{$fromStatus}' to '{$toStatus}'";
                $otherChanged = array_diff(array_keys($changes), ['status']);
                if (! empty($otherChanged)) {
                    $otherNames = implode(', ', array_map(fn ($k) => str_replace('_', ' ', $k), $otherChanged));
                    $actionDescription .= ' (also updated: '.Str::limit($otherNames, 100).')';
                }
            } else {
                $changedNames = implode(', ', array_map(fn ($k) => str_replace('_', ' ', $k), array_keys($changes)));
                $actionDescription = 'Updated: '.Str::limit($changedNames, 150);
            }

            ActionLog::create([
                'actionable_type' => $model->getMorphClass(),
                'actionable_id' => (string) $model->getKey(),
                'user_id' => $user?->id,
                'user_name' => $userName,
                'action' => $action,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'action_description' => $actionDescription,
                'changes' => $changes,
                'created_at' => now(),
            ]);
        });

        static::deleted(function (Model $model): void {
            $user = Auth::user();
            $userName = $user ? ($user->name ?? $user->email) : 'System / Auto';

            ActionLog::create([
                'actionable_type' => $model->getMorphClass(),
                'actionable_id' => (string) $model->getKey(),
                'user_id' => $user?->id,
                'user_name' => $userName,
                'action' => 'deleted',
                'from_status' => $model->getAttribute('status'),
                'to_status' => 'deleted',
                'action_description' => 'Record deleted',
                'changes' => null,
                'created_at' => now(),
            ]);
        });
    }

    public function logCustomAction(string $description, ?string $toStatus = null, ?string $fromStatus = null, ?array $changes = null, string $action = 'updated'): ActionLog
    {
        $user = Auth::user();
        $userName = $user ? ($user->name ?? $user->email) : 'System / Auto';

        return ActionLog::create([
            'actionable_type' => $this->getMorphClass(),
            'actionable_id' => (string) $this->getKey(),
            'user_id' => $user?->id,
            'user_name' => $userName,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'action_description' => $description,
            'changes' => $changes,
            'created_at' => now(),
        ]);
    }
}
