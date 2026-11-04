<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\NametagReturn;
use Illuminate\Auth\Access\HandlesAuthorization;

class NametagReturnPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:NametagReturn');
    }

    public function view(AuthUser $authUser, NametagReturn $nametagReturn): bool
    {
        return $authUser->can('View:NametagReturn');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:NametagReturn');
    }

    public function update(AuthUser $authUser, NametagReturn $nametagReturn): bool
    {
        return $authUser->can('Update:NametagReturn');
    }

    public function delete(AuthUser $authUser, NametagReturn $nametagReturn): bool
    {
        return $authUser->can('Delete:NametagReturn');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:NametagReturn');
    }

    public function restore(AuthUser $authUser, NametagReturn $nametagReturn): bool
    {
        return $authUser->can('Restore:NametagReturn');
    }

    public function forceDelete(AuthUser $authUser, NametagReturn $nametagReturn): bool
    {
        return $authUser->can('ForceDelete:NametagReturn');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:NametagReturn');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:NametagReturn');
    }

    public function replicate(AuthUser $authUser, NametagReturn $nametagReturn): bool
    {
        return $authUser->can('Replicate:NametagReturn');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:NametagReturn');
    }

}