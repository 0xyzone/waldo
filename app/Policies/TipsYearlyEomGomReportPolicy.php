<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TipsYearlyEomGomReport;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class TipsYearlyEomGomReportPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TipsYearlyEomGomReport');
    }

    public function view(AuthUser $authUser, TipsYearlyEomGomReport $tipsYearlyEomGomReport): bool
    {
        return $authUser->can('View:TipsYearlyEomGomReport');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TipsYearlyEomGomReport');
    }

    public function update(AuthUser $authUser, ?TipsYearlyEomGomReport $tipsYearlyEomGomReport = null): bool
    {
        return $authUser->can('Update:TipsYearlyEomGomReport');
    }

    public function edit(AuthUser $authUser, ?TipsYearlyEomGomReport $tipsYearlyEomGomReport = null): bool
    {
        return $this->update($authUser, $tipsYearlyEomGomReport);
    }

    public function delete(AuthUser $authUser, TipsYearlyEomGomReport $tipsYearlyEomGomReport): bool
    {
        return $authUser->can('Delete:TipsYearlyEomGomReport');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TipsYearlyEomGomReport');
    }

    public function restore(AuthUser $authUser, TipsYearlyEomGomReport $tipsYearlyEomGomReport): bool
    {
        return $authUser->can('Restore:TipsYearlyEomGomReport');
    }

    public function forceDelete(AuthUser $authUser, TipsYearlyEomGomReport $tipsYearlyEomGomReport): bool
    {
        return $authUser->can('ForceDelete:TipsYearlyEomGomReport');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TipsYearlyEomGomReport');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TipsYearlyEomGomReport');
    }

    public function replicate(AuthUser $authUser, TipsYearlyEomGomReport $tipsYearlyEomGomReport): bool
    {
        return $authUser->can('Replicate:TipsYearlyEomGomReport');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TipsYearlyEomGomReport');
    }
}
