<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\NametagDistribution;
use Illuminate\Auth\Access\HandlesAuthorization;

class NametagDistributionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:NametagDistribution');
    }

    public function view(AuthUser $authUser, NametagDistribution $nametagDistribution): bool
    {
        return $authUser->can('View:NametagDistribution');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:NametagDistribution');
    }

    public function update(AuthUser $authUser, NametagDistribution $nametagDistribution): bool
    {
        return $authUser->can('Update:NametagDistribution');
    }

    public function delete(AuthUser $authUser, NametagDistribution $nametagDistribution): bool
    {
        return $authUser->can('Delete:NametagDistribution');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:NametagDistribution');
    }

    public function restore(AuthUser $authUser, NametagDistribution $nametagDistribution): bool
    {
        return $authUser->can('Restore:NametagDistribution');
    }

    public function forceDelete(AuthUser $authUser, NametagDistribution $nametagDistribution): bool
    {
        return $authUser->can('ForceDelete:NametagDistribution');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:NametagDistribution');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:NametagDistribution');
    }

    public function replicate(AuthUser $authUser, NametagDistribution $nametagDistribution): bool
    {
        return $authUser->can('Replicate:NametagDistribution');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:NametagDistribution');
    }

}