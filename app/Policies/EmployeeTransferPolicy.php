<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EmployeeTransfer;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeeTransferPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EmployeeTransfer');
    }

    public function view(AuthUser $authUser, EmployeeTransfer $employeeTransfer): bool
    {
        return $authUser->can('View:EmployeeTransfer');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EmployeeTransfer');
    }

    public function update(AuthUser $authUser, EmployeeTransfer $employeeTransfer): bool
    {
        return $authUser->can('Update:EmployeeTransfer');
    }

    public function delete(AuthUser $authUser, EmployeeTransfer $employeeTransfer): bool
    {
        return $authUser->can('Delete:EmployeeTransfer');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EmployeeTransfer');
    }

    public function restore(AuthUser $authUser, EmployeeTransfer $employeeTransfer): bool
    {
        return $authUser->can('Restore:EmployeeTransfer');
    }

    public function forceDelete(AuthUser $authUser, EmployeeTransfer $employeeTransfer): bool
    {
        return $authUser->can('ForceDelete:EmployeeTransfer');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EmployeeTransfer');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EmployeeTransfer');
    }

    public function replicate(AuthUser $authUser, EmployeeTransfer $employeeTransfer): bool
    {
        return $authUser->can('Replicate:EmployeeTransfer');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EmployeeTransfer');
    }

}