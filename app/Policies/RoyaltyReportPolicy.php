<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RoyaltyReport;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoyaltyReportPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RoyaltyReport');
    }

    public function view(AuthUser $authUser, RoyaltyReport $royaltyReport): bool
    {
        return $authUser->can('View:RoyaltyReport');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RoyaltyReport');
    }

    public function update(AuthUser $authUser, RoyaltyReport $royaltyReport): bool
    {
        return $authUser->can('Update:RoyaltyReport');
    }

    public function delete(AuthUser $authUser, RoyaltyReport $royaltyReport): bool
    {
        return $authUser->can('Delete:RoyaltyReport');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RoyaltyReport');
    }

    public function restore(AuthUser $authUser, RoyaltyReport $royaltyReport): bool
    {
        return $authUser->can('Restore:RoyaltyReport');
    }

    public function forceDelete(AuthUser $authUser, RoyaltyReport $royaltyReport): bool
    {
        return $authUser->can('ForceDelete:RoyaltyReport');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RoyaltyReport');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RoyaltyReport');
    }

    public function replicate(AuthUser $authUser, RoyaltyReport $royaltyReport): bool
    {
        return $authUser->can('Replicate:RoyaltyReport');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RoyaltyReport');
    }

}