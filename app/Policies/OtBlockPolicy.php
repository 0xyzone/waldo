<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OtBlock;
use Illuminate\Auth\Access\HandlesAuthorization;

class OtBlockPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OtBlock');
    }

    public function view(AuthUser $authUser, OtBlock $otBlock): bool
    {
        return $authUser->can('View:OtBlock');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OtBlock');
    }

    public function update(AuthUser $authUser, OtBlock $otBlock): bool
    {
        return $authUser->can('Update:OtBlock');
    }

    public function delete(AuthUser $authUser, OtBlock $otBlock): bool
    {
        return $authUser->can('Delete:OtBlock');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:OtBlock');
    }

    public function restore(AuthUser $authUser, OtBlock $otBlock): bool
    {
        return $authUser->can('Restore:OtBlock');
    }

    public function forceDelete(AuthUser $authUser, OtBlock $otBlock): bool
    {
        return $authUser->can('ForceDelete:OtBlock');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OtBlock');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OtBlock');
    }

    public function replicate(AuthUser $authUser, OtBlock $otBlock): bool
    {
        return $authUser->can('Replicate:OtBlock');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OtBlock');
    }

}