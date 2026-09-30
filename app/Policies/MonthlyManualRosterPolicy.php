<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MonthlyManualRoster;
use Illuminate\Auth\Access\HandlesAuthorization;

class MonthlyManualRosterPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MonthlyManualRoster');
    }

    public function view(AuthUser $authUser, MonthlyManualRoster $monthlyManualRoster): bool
    {
        return $authUser->can('View:MonthlyManualRoster');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MonthlyManualRoster');
    }

    public function update(AuthUser $authUser, MonthlyManualRoster $monthlyManualRoster): bool
    {
        return $authUser->can('Update:MonthlyManualRoster');
    }

    public function delete(AuthUser $authUser, MonthlyManualRoster $monthlyManualRoster): bool
    {
        return $authUser->can('Delete:MonthlyManualRoster');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MonthlyManualRoster');
    }

    public function restore(AuthUser $authUser, MonthlyManualRoster $monthlyManualRoster): bool
    {
        return $authUser->can('Restore:MonthlyManualRoster');
    }

    public function forceDelete(AuthUser $authUser, MonthlyManualRoster $monthlyManualRoster): bool
    {
        return $authUser->can('ForceDelete:MonthlyManualRoster');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MonthlyManualRoster');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MonthlyManualRoster');
    }

    public function replicate(AuthUser $authUser, MonthlyManualRoster $monthlyManualRoster): bool
    {
        return $authUser->can('Replicate:MonthlyManualRoster');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MonthlyManualRoster');
    }

}