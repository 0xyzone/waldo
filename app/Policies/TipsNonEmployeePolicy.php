<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TipsNonEmployee;
use Illuminate\Auth\Access\HandlesAuthorization;

class TipsNonEmployeePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TipsNonEmployee');
    }

    public function view(AuthUser $authUser, TipsNonEmployee $tipsNonEmployee): bool
    {
        return $authUser->can('View:TipsNonEmployee');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TipsNonEmployee');
    }

    public function update(AuthUser $authUser, TipsNonEmployee $tipsNonEmployee): bool
    {
        return $authUser->can('Update:TipsNonEmployee');
    }

    public function delete(AuthUser $authUser, TipsNonEmployee $tipsNonEmployee): bool
    {
        return $authUser->can('Delete:TipsNonEmployee');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TipsNonEmployee');
    }

    public function restore(AuthUser $authUser, TipsNonEmployee $tipsNonEmployee): bool
    {
        return $authUser->can('Restore:TipsNonEmployee');
    }

    public function forceDelete(AuthUser $authUser, TipsNonEmployee $tipsNonEmployee): bool
    {
        return $authUser->can('ForceDelete:TipsNonEmployee');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TipsNonEmployee');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TipsNonEmployee');
    }

    public function replicate(AuthUser $authUser, TipsNonEmployee $tipsNonEmployee): bool
    {
        return $authUser->can('Replicate:TipsNonEmployee');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TipsNonEmployee');
    }

}