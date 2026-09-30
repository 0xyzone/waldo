<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SalaryIncrementRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalaryIncrementRequestPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SalaryIncrementRequest');
    }

    public function view(AuthUser $authUser, SalaryIncrementRequest $salaryIncrementRequest): bool
    {
        return $authUser->can('View:SalaryIncrementRequest');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SalaryIncrementRequest');
    }

    public function update(AuthUser $authUser, SalaryIncrementRequest $salaryIncrementRequest): bool
    {
        return $authUser->can('Update:SalaryIncrementRequest');
    }

    public function delete(AuthUser $authUser, SalaryIncrementRequest $salaryIncrementRequest): bool
    {
        return $authUser->can('Delete:SalaryIncrementRequest');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SalaryIncrementRequest');
    }

    public function restore(AuthUser $authUser, SalaryIncrementRequest $salaryIncrementRequest): bool
    {
        return $authUser->can('Restore:SalaryIncrementRequest');
    }

    public function forceDelete(AuthUser $authUser, SalaryIncrementRequest $salaryIncrementRequest): bool
    {
        return $authUser->can('ForceDelete:SalaryIncrementRequest');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SalaryIncrementRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SalaryIncrementRequest');
    }

    public function replicate(AuthUser $authUser, SalaryIncrementRequest $salaryIncrementRequest): bool
    {
        return $authUser->can('Replicate:SalaryIncrementRequest');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SalaryIncrementRequest');
    }

}