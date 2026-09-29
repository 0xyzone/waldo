<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ManualAttendanceEmployee;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ManualAttendanceEmployeePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('ViewAny:ManualAttendanceEmployee');
    }

    public function view(AuthUser $authUser, ManualAttendanceEmployee $manualAttendanceEmployee): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('View:ManualAttendanceEmployee');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('Create:ManualAttendanceEmployee');
    }

    public function update(AuthUser $authUser, ManualAttendanceEmployee $manualAttendanceEmployee): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('Update:ManualAttendanceEmployee');
    }

    public function delete(AuthUser $authUser, ManualAttendanceEmployee $manualAttendanceEmployee): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('Delete:ManualAttendanceEmployee');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('DeleteAny:ManualAttendanceEmployee');
    }

    public function restore(AuthUser $authUser, ManualAttendanceEmployee $manualAttendanceEmployee): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('Restore:ManualAttendanceEmployee');
    }

    public function forceDelete(AuthUser $authUser, ManualAttendanceEmployee $manualAttendanceEmployee): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('ForceDelete:ManualAttendanceEmployee');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('ForceDeleteAny:ManualAttendanceEmployee');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('RestoreAny:ManualAttendanceEmployee');
    }

    public function replicate(AuthUser $authUser, ManualAttendanceEmployee $manualAttendanceEmployee): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('Replicate:ManualAttendanceEmployee');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('Reorder:ManualAttendanceEmployee');
    }
}
