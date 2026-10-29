<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TerminatedEmployee;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class TerminatedEmployeePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TerminatedEmployee');
    }

    public function view(AuthUser $authUser, TerminatedEmployee $terminatedEmployee): bool
    {
        return $authUser->can('View:TerminatedEmployee');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TerminatedEmployee');
    }

    public function update(AuthUser $authUser, TerminatedEmployee $terminatedEmployee): bool
    {
        return $authUser->can('Update:TerminatedEmployee');
    }

    public function delete(AuthUser $authUser, TerminatedEmployee $terminatedEmployee): bool
    {
        return $authUser->can('Delete:TerminatedEmployee');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TerminatedEmployee');
    }

    public function restore(AuthUser $authUser, TerminatedEmployee $terminatedEmployee): bool
    {
        return $authUser->can('Restore:TerminatedEmployee');
    }

    public function forceDelete(AuthUser $authUser, TerminatedEmployee $terminatedEmployee): bool
    {
        return $authUser->can('ForceDelete:TerminatedEmployee');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TerminatedEmployee');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TerminatedEmployee');
    }

    public function replicate(AuthUser $authUser, TerminatedEmployee $terminatedEmployee): bool
    {
        return $authUser->can('Replicate:TerminatedEmployee');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TerminatedEmployee');
    }
}
