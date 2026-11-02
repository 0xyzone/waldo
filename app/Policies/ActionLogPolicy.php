<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ActionLog;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActionLogPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ActionLog');
    }

    public function view(AuthUser $authUser, ActionLog $actionLog): bool
    {
        return $authUser->can('View:ActionLog');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ActionLog');
    }

    public function update(AuthUser $authUser, ActionLog $actionLog): bool
    {
        return $authUser->can('Update:ActionLog');
    }

    public function delete(AuthUser $authUser, ActionLog $actionLog): bool
    {
        return $authUser->can('Delete:ActionLog');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ActionLog');
    }

    public function restore(AuthUser $authUser, ActionLog $actionLog): bool
    {
        return $authUser->can('Restore:ActionLog');
    }

    public function forceDelete(AuthUser $authUser, ActionLog $actionLog): bool
    {
        return $authUser->can('ForceDelete:ActionLog');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ActionLog');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ActionLog');
    }

    public function replicate(AuthUser $authUser, ActionLog $actionLog): bool
    {
        return $authUser->can('Replicate:ActionLog');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ActionLog');
    }

}