<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ManualAttendanceEmployee;
use Illuminate\Auth\Access\HandlesAuthorization;

class ManualAttendanceEmployeePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ManualAttendanceEmployee');
    }

    public function view(AuthUser $authUser, ManualAttendanceEmployee $manualAttendanceEmployee): bool
    {
        return $authUser->can('View:ManualAttendanceEmployee');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ManualAttendanceEmployee');
    }

    public function update(AuthUser $authUser, ManualAttendanceEmployee $manualAttendanceEmployee): bool
    {
        return $authUser->can('Update:ManualAttendanceEmployee');
    }

    public function delete(AuthUser $authUser, ManualAttendanceEmployee $manualAttendanceEmployee): bool
    {
        return $authUser->can('Delete:ManualAttendanceEmployee');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ManualAttendanceEmployee');
    }

    public function restore(AuthUser $authUser, ManualAttendanceEmployee $manualAttendanceEmployee): bool
    {
        return $authUser->can('Restore:ManualAttendanceEmployee');
    }

    public function forceDelete(AuthUser $authUser, ManualAttendanceEmployee $manualAttendanceEmployee): bool
    {
        return $authUser->can('ForceDelete:ManualAttendanceEmployee');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ManualAttendanceEmployee');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ManualAttendanceEmployee');
    }

    public function replicate(AuthUser $authUser, ManualAttendanceEmployee $manualAttendanceEmployee): bool
    {
        return $authUser->can('Replicate:ManualAttendanceEmployee');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ManualAttendanceEmployee');
    }

}