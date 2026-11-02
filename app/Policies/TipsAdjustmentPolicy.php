<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TipsAdjustment;
use Illuminate\Auth\Access\HandlesAuthorization;

class TipsAdjustmentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TipsAdjustment');
    }

    public function view(AuthUser $authUser, TipsAdjustment $tipsAdjustment): bool
    {
        return $authUser->can('View:TipsAdjustment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TipsAdjustment');
    }

    public function update(AuthUser $authUser, TipsAdjustment $tipsAdjustment): bool
    {
        return $authUser->can('Update:TipsAdjustment');
    }

    public function delete(AuthUser $authUser, TipsAdjustment $tipsAdjustment): bool
    {
        return $authUser->can('Delete:TipsAdjustment');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TipsAdjustment');
    }

    public function restore(AuthUser $authUser, TipsAdjustment $tipsAdjustment): bool
    {
        return $authUser->can('Restore:TipsAdjustment');
    }

    public function forceDelete(AuthUser $authUser, TipsAdjustment $tipsAdjustment): bool
    {
        return $authUser->can('ForceDelete:TipsAdjustment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TipsAdjustment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TipsAdjustment');
    }

    public function replicate(AuthUser $authUser, TipsAdjustment $tipsAdjustment): bool
    {
        return $authUser->can('Replicate:TipsAdjustment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TipsAdjustment');
    }

}