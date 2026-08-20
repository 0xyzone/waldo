<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EmployeeSuspension;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeeSuspensionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EmployeeSuspension');
    }

    public function view(AuthUser $authUser, EmployeeSuspension $employeeSuspension): bool
    {
        return $authUser->can('View:EmployeeSuspension');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EmployeeSuspension');
    }

    public function update(AuthUser $authUser, EmployeeSuspension $employeeSuspension): bool
    {
        return $authUser->can('Update:EmployeeSuspension');
    }

    public function delete(AuthUser $authUser, EmployeeSuspension $employeeSuspension): bool
    {
        return $authUser->can('Delete:EmployeeSuspension');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EmployeeSuspension');
    }

    public function restore(AuthUser $authUser, EmployeeSuspension $employeeSuspension): bool
    {
        return $authUser->can('Restore:EmployeeSuspension');
    }

    public function forceDelete(AuthUser $authUser, EmployeeSuspension $employeeSuspension): bool
    {
        return $authUser->can('ForceDelete:EmployeeSuspension');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EmployeeSuspension');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EmployeeSuspension');
    }

    public function replicate(AuthUser $authUser, EmployeeSuspension $employeeSuspension): bool
    {
        return $authUser->can('Replicate:EmployeeSuspension');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EmployeeSuspension');
    }

}