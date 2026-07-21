<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MapUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class MapUserPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MapUser');
    }

    public function view(AuthUser $authUser, MapUser $mapUser): bool
    {
        return $authUser->can('View:MapUser');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MapUser');
    }

    public function update(AuthUser $authUser, MapUser $mapUser): bool
    {
        return $authUser->can('Update:MapUser');
    }

    public function delete(AuthUser $authUser, MapUser $mapUser): bool
    {
        return $authUser->can('Delete:MapUser');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MapUser');
    }

    public function restore(AuthUser $authUser, MapUser $mapUser): bool
    {
        return $authUser->can('Restore:MapUser');
    }

    public function forceDelete(AuthUser $authUser, MapUser $mapUser): bool
    {
        return $authUser->can('ForceDelete:MapUser');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MapUser');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MapUser');
    }

    public function replicate(AuthUser $authUser, MapUser $mapUser): bool
    {
        return $authUser->can('Replicate:MapUser');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MapUser');
    }

}