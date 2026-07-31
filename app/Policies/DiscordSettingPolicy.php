<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DiscordSetting;
use Illuminate\Auth\Access\HandlesAuthorization;

class DiscordSettingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DiscordSetting');
    }

    public function view(AuthUser $authUser, DiscordSetting $discordSetting): bool
    {
        return $authUser->can('View:DiscordSetting');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DiscordSetting');
    }

    public function update(AuthUser $authUser, DiscordSetting $discordSetting): bool
    {
        return $authUser->can('Update:DiscordSetting');
    }

    public function delete(AuthUser $authUser, DiscordSetting $discordSetting): bool
    {
        return $authUser->can('Delete:DiscordSetting');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DiscordSetting');
    }

    public function restore(AuthUser $authUser, DiscordSetting $discordSetting): bool
    {
        return $authUser->can('Restore:DiscordSetting');
    }

    public function forceDelete(AuthUser $authUser, DiscordSetting $discordSetting): bool
    {
        return $authUser->can('ForceDelete:DiscordSetting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DiscordSetting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DiscordSetting');
    }

    public function replicate(AuthUser $authUser, DiscordSetting $discordSetting): bool
    {
        return $authUser->can('Replicate:DiscordSetting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DiscordSetting');
    }

}