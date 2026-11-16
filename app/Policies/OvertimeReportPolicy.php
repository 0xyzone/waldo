<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OvertimeReport;
use Illuminate\Auth\Access\HandlesAuthorization;

class OvertimeReportPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OvertimeReport');
    }

    public function view(AuthUser $authUser, OvertimeReport $overtimeReport): bool
    {
        return $authUser->can('View:OvertimeReport');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OvertimeReport');
    }

    public function update(AuthUser $authUser, OvertimeReport $overtimeReport): bool
    {
        return $authUser->can('Update:OvertimeReport');
    }

    public function delete(AuthUser $authUser, OvertimeReport $overtimeReport): bool
    {
        return $authUser->can('Delete:OvertimeReport');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:OvertimeReport');
    }

    public function restore(AuthUser $authUser, OvertimeReport $overtimeReport): bool
    {
        return $authUser->can('Restore:OvertimeReport');
    }

    public function forceDelete(AuthUser $authUser, OvertimeReport $overtimeReport): bool
    {
        return $authUser->can('ForceDelete:OvertimeReport');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OvertimeReport');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OvertimeReport');
    }

    public function replicate(AuthUser $authUser, OvertimeReport $overtimeReport): bool
    {
        return $authUser->can('Replicate:OvertimeReport');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OvertimeReport');
    }

}