<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\IdCardPrintReport;
use Illuminate\Auth\Access\HandlesAuthorization;

class IdCardPrintReportPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:IdCardPrintReport');
    }

    public function view(AuthUser $authUser, IdCardPrintReport $idCardPrintReport): bool
    {
        return $authUser->can('View:IdCardPrintReport');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:IdCardPrintReport');
    }

    public function update(AuthUser $authUser, IdCardPrintReport $idCardPrintReport): bool
    {
        return $authUser->can('Update:IdCardPrintReport');
    }

    public function delete(AuthUser $authUser, IdCardPrintReport $idCardPrintReport): bool
    {
        return $authUser->can('Delete:IdCardPrintReport');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:IdCardPrintReport');
    }

    public function restore(AuthUser $authUser, IdCardPrintReport $idCardPrintReport): bool
    {
        return $authUser->can('Restore:IdCardPrintReport');
    }

    public function forceDelete(AuthUser $authUser, IdCardPrintReport $idCardPrintReport): bool
    {
        return $authUser->can('ForceDelete:IdCardPrintReport');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:IdCardPrintReport');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:IdCardPrintReport');
    }

    public function replicate(AuthUser $authUser, IdCardPrintReport $idCardPrintReport): bool
    {
        return $authUser->can('Replicate:IdCardPrintReport');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:IdCardPrintReport');
    }

}