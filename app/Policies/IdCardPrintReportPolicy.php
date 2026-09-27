<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\IdCardPrintReport;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class IdCardPrintReportPolicy
{
    use HandlesAuthorization;

    public function before(AuthUser $authUser, string $ability): ?bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:IdCardPrintReport') || $authUser->hasRole(['super_admin', 'IT']);
    }

    public function view(AuthUser $authUser, IdCardPrintReport $idCardPrintReport): bool
    {
        return $authUser->can('View:IdCardPrintReport') || $authUser->hasRole(['super_admin', 'IT']);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:IdCardPrintReport') || $authUser->hasRole(['super_admin', 'IT']);
    }

    public function update(AuthUser $authUser, IdCardPrintReport $idCardPrintReport): bool
    {
        return $authUser->can('Update:IdCardPrintReport') || $authUser->hasRole(['super_admin', 'IT']);
    }

    public function delete(AuthUser $authUser, IdCardPrintReport $idCardPrintReport): bool
    {
        return $authUser->can('Delete:IdCardPrintReport') || $authUser->hasRole(['super_admin', 'IT']);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:IdCardPrintReport') || $authUser->hasRole(['super_admin', 'IT']);
    }

    public function restore(AuthUser $authUser, IdCardPrintReport $idCardPrintReport): bool
    {
        return $authUser->can('Restore:IdCardPrintReport') || $authUser->hasRole(['super_admin', 'IT']);
    }

    public function forceDelete(AuthUser $authUser, IdCardPrintReport $idCardPrintReport): bool
    {
        return $authUser->can('ForceDelete:IdCardPrintReport') || $authUser->hasRole(['super_admin', 'IT']);
    }
}
