<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TipsReport;
use Illuminate\Auth\Access\HandlesAuthorization;

class TipsReportPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TipsReport');
    }

    public function view(AuthUser $authUser, TipsReport $tipsReport): bool
    {
        return $authUser->can('View:TipsReport');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TipsReport');
    }

    public function update(AuthUser $authUser, TipsReport $tipsReport): bool
    {
        return $authUser->can('Update:TipsReport');
    }

    public function delete(AuthUser $authUser, TipsReport $tipsReport): bool
    {
        return $authUser->can('Delete:TipsReport');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TipsReport');
    }

    public function restore(AuthUser $authUser, TipsReport $tipsReport): bool
    {
        return $authUser->can('Restore:TipsReport');
    }

    public function forceDelete(AuthUser $authUser, TipsReport $tipsReport): bool
    {
        return $authUser->can('ForceDelete:TipsReport');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TipsReport');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TipsReport');
    }

    public function replicate(AuthUser $authUser, TipsReport $tipsReport): bool
    {
        return $authUser->can('Replicate:TipsReport');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TipsReport');
    }

}