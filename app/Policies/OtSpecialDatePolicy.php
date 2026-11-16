<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OtSpecialDate;
use Illuminate\Auth\Access\HandlesAuthorization;

class OtSpecialDatePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OtSpecialDate');
    }

    public function view(AuthUser $authUser, OtSpecialDate $otSpecialDate): bool
    {
        return $authUser->can('View:OtSpecialDate');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OtSpecialDate');
    }

    public function update(AuthUser $authUser, OtSpecialDate $otSpecialDate): bool
    {
        return $authUser->can('Update:OtSpecialDate');
    }

    public function delete(AuthUser $authUser, OtSpecialDate $otSpecialDate): bool
    {
        return $authUser->can('Delete:OtSpecialDate');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:OtSpecialDate');
    }

    public function restore(AuthUser $authUser, OtSpecialDate $otSpecialDate): bool
    {
        return $authUser->can('Restore:OtSpecialDate');
    }

    public function forceDelete(AuthUser $authUser, OtSpecialDate $otSpecialDate): bool
    {
        return $authUser->can('ForceDelete:OtSpecialDate');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OtSpecialDate');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OtSpecialDate');
    }

    public function replicate(AuthUser $authUser, OtSpecialDate $otSpecialDate): bool
    {
        return $authUser->can('Replicate:OtSpecialDate');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OtSpecialDate');
    }

}