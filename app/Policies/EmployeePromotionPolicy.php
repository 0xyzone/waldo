<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EmployeePromotion;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeePromotionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EmployeePromotion');
    }

    public function view(AuthUser $authUser, EmployeePromotion $employeePromotion): bool
    {
        return $authUser->can('View:EmployeePromotion');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EmployeePromotion');
    }

    public function update(AuthUser $authUser, EmployeePromotion $employeePromotion): bool
    {
        return $authUser->can('Update:EmployeePromotion');
    }

    public function delete(AuthUser $authUser, EmployeePromotion $employeePromotion): bool
    {
        return $authUser->can('Delete:EmployeePromotion');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EmployeePromotion');
    }

    public function restore(AuthUser $authUser, EmployeePromotion $employeePromotion): bool
    {
        return $authUser->can('Restore:EmployeePromotion');
    }

    public function forceDelete(AuthUser $authUser, EmployeePromotion $employeePromotion): bool
    {
        return $authUser->can('ForceDelete:EmployeePromotion');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EmployeePromotion');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EmployeePromotion');
    }

    public function replicate(AuthUser $authUser, EmployeePromotion $employeePromotion): bool
    {
        return $authUser->can('Replicate:EmployeePromotion');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EmployeePromotion');
    }

}