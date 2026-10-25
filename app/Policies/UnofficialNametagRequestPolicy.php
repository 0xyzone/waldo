<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\UnofficialNametagRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class UnofficialNametagRequestPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:UnofficialNametagRequest');
    }

    public function view(AuthUser $authUser, UnofficialNametagRequest $unofficialNametagRequest): bool
    {
        return $authUser->can('View:UnofficialNametagRequest');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:UnofficialNametagRequest');
    }

    public function update(AuthUser $authUser, UnofficialNametagRequest $unofficialNametagRequest): bool
    {
        return $authUser->can('Update:UnofficialNametagRequest');
    }

    public function delete(AuthUser $authUser, UnofficialNametagRequest $unofficialNametagRequest): bool
    {
        return $authUser->can('Delete:UnofficialNametagRequest');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:UnofficialNametagRequest');
    }

    public function restore(AuthUser $authUser, UnofficialNametagRequest $unofficialNametagRequest): bool
    {
        return $authUser->can('Restore:UnofficialNametagRequest');
    }

    public function forceDelete(AuthUser $authUser, UnofficialNametagRequest $unofficialNametagRequest): bool
    {
        return $authUser->can('ForceDelete:UnofficialNametagRequest');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:UnofficialNametagRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:UnofficialNametagRequest');
    }

    public function replicate(AuthUser $authUser, UnofficialNametagRequest $unofficialNametagRequest): bool
    {
        return $authUser->can('Replicate:UnofficialNametagRequest');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:UnofficialNametagRequest');
    }

}