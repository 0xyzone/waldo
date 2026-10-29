<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\IdCardRequest;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class IdCardRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:IdCardRequest');
    }

    public function view(AuthUser $authUser, IdCardRequest $idCardRequest): bool
    {
        return $authUser->can('View:IdCardRequest');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:IdCardRequest');
    }

    public function update(AuthUser $authUser, IdCardRequest $idCardRequest): bool
    {
        return $authUser->can('Update:IdCardRequest');
    }

    public function delete(AuthUser $authUser, IdCardRequest $idCardRequest): bool
    {
        return $authUser->can('Delete:IdCardRequest');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:IdCardRequest');
    }

    public function restore(AuthUser $authUser, IdCardRequest $idCardRequest): bool
    {
        return $authUser->can('Restore:IdCardRequest');
    }

    public function forceDelete(AuthUser $authUser, IdCardRequest $idCardRequest): bool
    {
        return $authUser->can('ForceDelete:IdCardRequest');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:IdCardRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:IdCardRequest');
    }

    public function replicate(AuthUser $authUser, IdCardRequest $idCardRequest): bool
    {
        return $authUser->can('Replicate:IdCardRequest');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:IdCardRequest');
    }
}
