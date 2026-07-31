<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Leaver;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class LeaverPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Leaver');
    }

    public function view(AuthUser $authUser, Leaver $leaver): bool
    {
        return $authUser->can('View:Leaver');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Leaver');
    }

    public function update(AuthUser $authUser, Leaver $leaver): bool
    {
        return $authUser->can('Update:Leaver');
    }

    public function delete(AuthUser $authUser, Leaver $leaver): bool
    {
        return $authUser->can('Delete:Leaver');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Leaver');
    }

    public function restore(AuthUser $authUser, Leaver $leaver): bool
    {
        return $authUser->can('Restore:Leaver');
    }

    public function forceDelete(AuthUser $authUser, Leaver $leaver): bool
    {
        return $authUser->can('ForceDelete:Leaver');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Leaver');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Leaver');
    }

    public function replicate(AuthUser $authUser, Leaver $leaver): bool
    {
        return $authUser->can('Replicate:Leaver');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Leaver');
    }
}
