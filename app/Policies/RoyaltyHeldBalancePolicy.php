<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RoyaltyHeldBalance;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class RoyaltyHeldBalancePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RoyaltyHeldBalance');
    }

    public function view(AuthUser $authUser, RoyaltyHeldBalance $royaltyHeldBalance): bool
    {
        return $authUser->can('View:RoyaltyHeldBalance');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RoyaltyHeldBalance');
    }

    public function update(AuthUser $authUser, RoyaltyHeldBalance $royaltyHeldBalance): bool
    {
        return $authUser->can('Update:RoyaltyHeldBalance');
    }

    public function delete(AuthUser $authUser, RoyaltyHeldBalance $royaltyHeldBalance): bool
    {
        return $authUser->can('Delete:RoyaltyHeldBalance');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RoyaltyHeldBalance');
    }

    public function restore(AuthUser $authUser, RoyaltyHeldBalance $royaltyHeldBalance): bool
    {
        return $authUser->can('Restore:RoyaltyHeldBalance');
    }

    public function forceDelete(AuthUser $authUser, RoyaltyHeldBalance $royaltyHeldBalance): bool
    {
        return $authUser->can('ForceDelete:RoyaltyHeldBalance');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RoyaltyHeldBalance');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RoyaltyHeldBalance');
    }

    public function replicate(AuthUser $authUser, RoyaltyHeldBalance $royaltyHeldBalance): bool
    {
        return $authUser->can('Replicate:RoyaltyHeldBalance');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RoyaltyHeldBalance');
    }
}
