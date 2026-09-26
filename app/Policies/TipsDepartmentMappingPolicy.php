<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TipsDepartmentMapping;
use Illuminate\Auth\Access\HandlesAuthorization;

class TipsDepartmentMappingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TipsDepartmentMapping');
    }

    public function view(AuthUser $authUser, TipsDepartmentMapping $tipsDepartmentMapping): bool
    {
        return $authUser->can('View:TipsDepartmentMapping');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TipsDepartmentMapping');
    }

    public function update(AuthUser $authUser, TipsDepartmentMapping $tipsDepartmentMapping): bool
    {
        return $authUser->can('Update:TipsDepartmentMapping');
    }

    public function delete(AuthUser $authUser, TipsDepartmentMapping $tipsDepartmentMapping): bool
    {
        return $authUser->can('Delete:TipsDepartmentMapping');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TipsDepartmentMapping');
    }

    public function restore(AuthUser $authUser, TipsDepartmentMapping $tipsDepartmentMapping): bool
    {
        return $authUser->can('Restore:TipsDepartmentMapping');
    }

    public function forceDelete(AuthUser $authUser, TipsDepartmentMapping $tipsDepartmentMapping): bool
    {
        return $authUser->can('ForceDelete:TipsDepartmentMapping');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TipsDepartmentMapping');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TipsDepartmentMapping');
    }

    public function replicate(AuthUser $authUser, TipsDepartmentMapping $tipsDepartmentMapping): bool
    {
        return $authUser->can('Replicate:TipsDepartmentMapping');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TipsDepartmentMapping');
    }

}