<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OvertimeEntry;
use Illuminate\Auth\Access\HandlesAuthorization;

class OvertimeEntryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OvertimeEntry');
    }

    public function view(AuthUser $authUser, OvertimeEntry $overtimeEntry): bool
    {
        return $authUser->can('View:OvertimeEntry');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OvertimeEntry');
    }

    public function update(AuthUser $authUser, OvertimeEntry $overtimeEntry): bool
    {
        return $authUser->can('Update:OvertimeEntry');
    }

    public function delete(AuthUser $authUser, OvertimeEntry $overtimeEntry): bool
    {
        return $authUser->can('Delete:OvertimeEntry');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:OvertimeEntry');
    }

    public function restore(AuthUser $authUser, OvertimeEntry $overtimeEntry): bool
    {
        return $authUser->can('Restore:OvertimeEntry');
    }

    public function forceDelete(AuthUser $authUser, OvertimeEntry $overtimeEntry): bool
    {
        return $authUser->can('ForceDelete:OvertimeEntry');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OvertimeEntry');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OvertimeEntry');
    }

    public function replicate(AuthUser $authUser, OvertimeEntry $overtimeEntry): bool
    {
        return $authUser->can('Replicate:OvertimeEntry');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OvertimeEntry');
    }

}