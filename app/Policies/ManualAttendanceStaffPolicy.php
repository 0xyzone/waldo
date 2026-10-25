<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ManualAttendanceStaff;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ManualAttendanceStaffPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ManualAttendanceStaff');
    }

    public function view(AuthUser $authUser, ManualAttendanceStaff $manualAttendanceStaff): bool
    {
        return $authUser->can('View:ManualAttendanceStaff');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ManualAttendanceStaff');
    }

    public function update(AuthUser $authUser, ManualAttendanceStaff $manualAttendanceStaff): bool
    {
        return $authUser->can('Update:ManualAttendanceStaff');
    }

    public function delete(AuthUser $authUser, ManualAttendanceStaff $manualAttendanceStaff): bool
    {
        return $authUser->can('Delete:ManualAttendanceStaff');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ManualAttendanceStaff');
    }

    public function restore(AuthUser $authUser, ManualAttendanceStaff $manualAttendanceStaff): bool
    {
        return $authUser->can('Restore:ManualAttendanceStaff');
    }

    public function forceDelete(AuthUser $authUser, ManualAttendanceStaff $manualAttendanceStaff): bool
    {
        return $authUser->can('ForceDelete:ManualAttendanceStaff');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ManualAttendanceStaff');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ManualAttendanceStaff');
    }

    public function replicate(AuthUser $authUser, ManualAttendanceStaff $manualAttendanceStaff): bool
    {
        return $authUser->can('Replicate:ManualAttendanceStaff');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ManualAttendanceStaff');
    }
}
