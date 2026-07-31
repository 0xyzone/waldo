<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BiometricAllotment;
use Illuminate\Auth\Access\HandlesAuthorization;

class BiometricAllotmentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BiometricAllotment');
    }

    public function view(AuthUser $authUser, BiometricAllotment $biometricAllotment): bool
    {
        return $authUser->can('View:BiometricAllotment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BiometricAllotment');
    }

    public function update(AuthUser $authUser, BiometricAllotment $biometricAllotment): bool
    {
        return $authUser->can('Update:BiometricAllotment');
    }

    public function delete(AuthUser $authUser, BiometricAllotment $biometricAllotment): bool
    {
        return $authUser->can('Delete:BiometricAllotment');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:BiometricAllotment');
    }

    public function restore(AuthUser $authUser, BiometricAllotment $biometricAllotment): bool
    {
        return $authUser->can('Restore:BiometricAllotment');
    }

    public function forceDelete(AuthUser $authUser, BiometricAllotment $biometricAllotment): bool
    {
        return $authUser->can('ForceDelete:BiometricAllotment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BiometricAllotment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BiometricAllotment');
    }

    public function replicate(AuthUser $authUser, BiometricAllotment $biometricAllotment): bool
    {
        return $authUser->can('Replicate:BiometricAllotment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BiometricAllotment');
    }

}