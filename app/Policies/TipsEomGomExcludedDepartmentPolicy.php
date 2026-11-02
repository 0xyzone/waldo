<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TipsEomGomExcludedDepartment;
use Illuminate\Auth\Access\HandlesAuthorization;

class TipsEomGomExcludedDepartmentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TipsEomGomExcludedDepartment');
    }

    public function view(AuthUser $authUser, TipsEomGomExcludedDepartment $tipsEomGomExcludedDepartment): bool
    {
        return $authUser->can('View:TipsEomGomExcludedDepartment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TipsEomGomExcludedDepartment');
    }

    public function update(AuthUser $authUser, TipsEomGomExcludedDepartment $tipsEomGomExcludedDepartment): bool
    {
        return $authUser->can('Update:TipsEomGomExcludedDepartment');
    }

    public function delete(AuthUser $authUser, TipsEomGomExcludedDepartment $tipsEomGomExcludedDepartment): bool
    {
        return $authUser->can('Delete:TipsEomGomExcludedDepartment');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TipsEomGomExcludedDepartment');
    }

    public function restore(AuthUser $authUser, TipsEomGomExcludedDepartment $tipsEomGomExcludedDepartment): bool
    {
        return $authUser->can('Restore:TipsEomGomExcludedDepartment');
    }

    public function forceDelete(AuthUser $authUser, TipsEomGomExcludedDepartment $tipsEomGomExcludedDepartment): bool
    {
        return $authUser->can('ForceDelete:TipsEomGomExcludedDepartment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TipsEomGomExcludedDepartment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TipsEomGomExcludedDepartment');
    }

    public function replicate(AuthUser $authUser, TipsEomGomExcludedDepartment $tipsEomGomExcludedDepartment): bool
    {
        return $authUser->can('Replicate:TipsEomGomExcludedDepartment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TipsEomGomExcludedDepartment');
    }

}