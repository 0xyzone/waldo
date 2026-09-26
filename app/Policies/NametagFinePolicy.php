<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\NametagFine;
use Illuminate\Auth\Access\HandlesAuthorization;

class NametagFinePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:NametagFine');
    }

    public function view(AuthUser $authUser, NametagFine $nametagFine): bool
    {
        return $authUser->can('View:NametagFine');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:NametagFine');
    }

    public function update(AuthUser $authUser, NametagFine $nametagFine): bool
    {
        return $authUser->can('Update:NametagFine');
    }

    public function delete(AuthUser $authUser, NametagFine $nametagFine): bool
    {
        return $authUser->can('Delete:NametagFine');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:NametagFine');
    }

    public function restore(AuthUser $authUser, NametagFine $nametagFine): bool
    {
        return $authUser->can('Restore:NametagFine');
    }

    public function forceDelete(AuthUser $authUser, NametagFine $nametagFine): bool
    {
        return $authUser->can('ForceDelete:NametagFine');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:NametagFine');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:NametagFine');
    }

    public function replicate(AuthUser $authUser, NametagFine $nametagFine): bool
    {
        return $authUser->can('Replicate:NametagFine');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:NametagFine');
    }

}