<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MonthlyManualRoster;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MonthlyManualRosterPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('ViewAny:MonthlyManualRoster');
    }

    public function view(AuthUser $authUser, MonthlyManualRoster $monthlyManualRoster): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('View:MonthlyManualRoster');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('Create:MonthlyManualRoster');
    }

    public function update(AuthUser $authUser, MonthlyManualRoster $monthlyManualRoster): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('Update:MonthlyManualRoster');
    }

    public function delete(AuthUser $authUser, MonthlyManualRoster $monthlyManualRoster): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('Delete:MonthlyManualRoster');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('DeleteAny:MonthlyManualRoster');
    }

    public function restore(AuthUser $authUser, MonthlyManualRoster $monthlyManualRoster): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('Restore:MonthlyManualRoster');
    }

    public function forceDelete(AuthUser $authUser, MonthlyManualRoster $monthlyManualRoster): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('ForceDelete:MonthlyManualRoster');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('ForceDeleteAny:MonthlyManualRoster');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('RestoreAny:MonthlyManualRoster');
    }

    public function replicate(AuthUser $authUser, MonthlyManualRoster $monthlyManualRoster): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('Replicate:MonthlyManualRoster');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->hasRole(['super_admin', 'HR', 'HR Assist']) || $authUser->can('Reorder:MonthlyManualRoster');
    }
}
