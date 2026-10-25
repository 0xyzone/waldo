<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MonthlyManualAttendance;
use Illuminate\Auth\Access\HandlesAuthorization;

class MonthlyManualAttendancePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MonthlyManualAttendance');
    }

    public function view(AuthUser $authUser, MonthlyManualAttendance $monthlyManualAttendance): bool
    {
        return $authUser->can('View:MonthlyManualAttendance');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MonthlyManualAttendance');
    }

    public function update(AuthUser $authUser, MonthlyManualAttendance $monthlyManualAttendance): bool
    {
        return $authUser->can('Update:MonthlyManualAttendance');
    }

    public function delete(AuthUser $authUser, MonthlyManualAttendance $monthlyManualAttendance): bool
    {
        return $authUser->can('Delete:MonthlyManualAttendance');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MonthlyManualAttendance');
    }

    public function restore(AuthUser $authUser, MonthlyManualAttendance $monthlyManualAttendance): bool
    {
        return $authUser->can('Restore:MonthlyManualAttendance');
    }

    public function forceDelete(AuthUser $authUser, MonthlyManualAttendance $monthlyManualAttendance): bool
    {
        return $authUser->can('ForceDelete:MonthlyManualAttendance');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MonthlyManualAttendance');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MonthlyManualAttendance');
    }

    public function replicate(AuthUser $authUser, MonthlyManualAttendance $monthlyManualAttendance): bool
    {
        return $authUser->can('Replicate:MonthlyManualAttendance');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MonthlyManualAttendance');
    }

}