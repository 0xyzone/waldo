<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SalaryIncrementRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalaryIncrementRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $authUser): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist', 'Finance']) || $authUser->can('ViewAny:SalaryIncrementRequest');
    }

    public function view(User $authUser, SalaryIncrementRequest $salaryIncrementRequest): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist', 'Finance']) || $authUser->can('View:SalaryIncrementRequest');
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('Create:SalaryIncrementRequest');
    }

    public function update(User $authUser, SalaryIncrementRequest $salaryIncrementRequest): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist', 'Finance']) || $authUser->can('Update:SalaryIncrementRequest');
    }

    public function delete(User $authUser, SalaryIncrementRequest $salaryIncrementRequest): bool
    {
        return $authUser->hasRole(['super_admin', 'HR']) || $authUser->can('Delete:SalaryIncrementRequest');
    }

    public function deleteAny(User $authUser): bool
    {
        return $authUser->hasRole(['super_admin', 'HR']) || $authUser->can('DeleteAny:SalaryIncrementRequest');
    }

    public function restore(User $authUser, SalaryIncrementRequest $salaryIncrementRequest): bool
    {
        return $authUser->hasRole(['super_admin', 'HR']) || $authUser->can('Restore:SalaryIncrementRequest');
    }

    public function forceDelete(User $authUser, SalaryIncrementRequest $salaryIncrementRequest): bool
    {
        return $authUser->hasRole(['super_admin', 'HR']) || $authUser->can('ForceDelete:SalaryIncrementRequest');
    }

    public function forceDeleteAny(User $authUser): bool
    {
        return $authUser->hasRole(['super_admin', 'HR']) || $authUser->can('ForceDeleteAny:SalaryIncrementRequest');
    }

    public function restoreAny(User $authUser): bool
    {
        return $authUser->hasRole(['super_admin', 'HR']) || $authUser->can('RestoreAny:SalaryIncrementRequest');
    }

    public function replicate(User $authUser, SalaryIncrementRequest $salaryIncrementRequest): bool
    {
        return $authUser->hasRole(['super_admin', 'HR']) || $authUser->can('Replicate:SalaryIncrementRequest');
    }

    public function reorder(User $authUser): bool
    {
        return $authUser->hasRole(['super_admin', 'HR']) || $authUser->can('Reorder:SalaryIncrementRequest');
    }
}
