<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ScheduleRun;
use Illuminate\Auth\Access\HandlesAuthorization;

class ScheduleRunPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ScheduleRun');
    }

    public function view(AuthUser $authUser, ScheduleRun $scheduleRun): bool
    {
        return $authUser->can('View:ScheduleRun');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ScheduleRun');
    }

    public function update(AuthUser $authUser, ScheduleRun $scheduleRun): bool
    {
        return $authUser->can('Update:ScheduleRun');
    }

    public function delete(AuthUser $authUser, ScheduleRun $scheduleRun): bool
    {
        return $authUser->can('Delete:ScheduleRun');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ScheduleRun');
    }

    public function restore(AuthUser $authUser, ScheduleRun $scheduleRun): bool
    {
        return $authUser->can('Restore:ScheduleRun');
    }

    public function forceDelete(AuthUser $authUser, ScheduleRun $scheduleRun): bool
    {
        return $authUser->can('ForceDelete:ScheduleRun');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ScheduleRun');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ScheduleRun');
    }

    public function replicate(AuthUser $authUser, ScheduleRun $scheduleRun): bool
    {
        return $authUser->can('Replicate:ScheduleRun');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ScheduleRun');
    }

}