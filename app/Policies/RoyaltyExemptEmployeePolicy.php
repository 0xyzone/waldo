<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RoyaltyExemptEmployee;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoyaltyExemptEmployeePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RoyaltyExemptEmployee');
    }

    public function view(AuthUser $authUser, RoyaltyExemptEmployee $royaltyExemptEmployee): bool
    {
        return $authUser->can('View:RoyaltyExemptEmployee');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RoyaltyExemptEmployee');
    }

    public function update(AuthUser $authUser, RoyaltyExemptEmployee $royaltyExemptEmployee): bool
    {
        return $authUser->can('Update:RoyaltyExemptEmployee');
    }

    public function delete(AuthUser $authUser, RoyaltyExemptEmployee $royaltyExemptEmployee): bool
    {
        return $authUser->can('Delete:RoyaltyExemptEmployee');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RoyaltyExemptEmployee');
    }

    public function restore(AuthUser $authUser, RoyaltyExemptEmployee $royaltyExemptEmployee): bool
    {
        return $authUser->can('Restore:RoyaltyExemptEmployee');
    }

    public function forceDelete(AuthUser $authUser, RoyaltyExemptEmployee $royaltyExemptEmployee): bool
    {
        return $authUser->can('ForceDelete:RoyaltyExemptEmployee');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RoyaltyExemptEmployee');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RoyaltyExemptEmployee');
    }

    public function replicate(AuthUser $authUser, RoyaltyExemptEmployee $royaltyExemptEmployee): bool
    {
        return $authUser->can('Replicate:RoyaltyExemptEmployee');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RoyaltyExemptEmployee');
    }

}