<?php

namespace App\Services;

use App\Models\DiscordSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscordService
{
    /**
     * Get the active discord setting record.
     */
    public static function getSetting(): ?DiscordSetting
    {
        return DiscordSetting::first();
    }

    /**
     * Get general server (guild) info from Discord API.
     *
     * @return array<string, mixed>|null
     */
    public static function getGuildInfo(): ?array
    {
        $setting = self::getSetting();
        if (! $setting || ! $setting->bot_token || ! $setting->guild_id) {
            return null;
        }

        try {
            return Cache::remember("discord_guild_info_{$setting->id}", now()->addMinutes(10), function () use ($setting) {
                $response = Http::withoutVerifying()
                    ->withToken($setting->bot_token, 'Bot')
                    ->get("https://discord.com/api/v10/guilds/{$setting->guild_id}");

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error('Discord getGuildInfo API failed: '.$response->body());

                return null;
            });
        } catch (\Exception $e) {
            Log::error('Discord getGuildInfo exception: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Get channels from Discord API.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getChannels(): array
    {
        $setting = self::getSetting();
        if (! $setting || ! $setting->bot_token || ! $setting->guild_id) {
            return [];
        }

        try {
            return Cache::remember("discord_channels_raw_{$setting->id}", now()->addMinutes(10), function () use ($setting) {
                $response = Http::withoutVerifying()
                    ->withToken($setting->bot_token, 'Bot')
                    ->get("https://discord.com/api/v10/guilds/{$setting->guild_id}/channels");

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error('Discord getChannels API failed: '.$response->body());

                return [];
            });
        } catch (\Exception $e) {
            Log::error('Discord getChannels exception: '.$e->getMessage());
        }

        return [];
    }

    /**
     * Get categories and text channels grouped for select dropdown.
     *
     * @return array<string, array<string, string>>
     */
    public static function getChannelsGroupedByCategory(): array
    {
        $channels = self::getChannels();
        if (empty($channels)) {
            return [];
        }

        $categories = [];
        $textChannels = [];

        foreach ($channels as $channel) {
            if (($channel['type'] ?? null) === 4) {
                $categories[$channel['id']] = $channel['name'] ?? 'Unnamed Category';
            } elseif (in_array(($channel['type'] ?? null), [0, 5], true)) {
                $textChannels[] = $channel;
            }
        }

        $grouped = [];
        $uncategorized = [];

        foreach ($textChannels as $channel) {
            $parentId = $channel['parent_id'] ?? null;
            $isAnnouncement = ($channel['type'] ?? null) === 5;
            $channelPrefix = $isAnnouncement ? '📢 #' : '#';
            $channelName = $channelPrefix.($channel['name'] ?? 'unnamed');

            if ($parentId && isset($categories[$parentId])) {
                $categoryName = $categories[$parentId];
                $grouped[$categoryName][$channel['id']] = $channelName;
            } else {
                $uncategorized[$channel['id']] = $channelName;
            }
        }

        if (! empty($uncategorized)) {
            $grouped['Text Channels'] = $uncategorized;
        }

        return $grouped;
    }

    /**
     * Get server roles from Discord API.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getRoles(): array
    {
        $setting = self::getSetting();
        if (! $setting || ! $setting->bot_token || ! $setting->guild_id) {
            return [];
        }

        try {
            return Cache::remember("discord_roles_raw_{$setting->id}", now()->addMinutes(10), function () use ($setting) {
                $response = Http::withoutVerifying()
                    ->withToken($setting->bot_token, 'Bot')
                    ->get("https://discord.com/api/v10/guilds/{$setting->guild_id}/roles");

                if ($response->successful()) {
                    return $response->json();
                }

                Log::error('Discord getRoles API failed: '.$response->body());

                return [];
            });
        } catch (\Exception $e) {
            Log::error('Discord getRoles exception: '.$e->getMessage());
        }

        return [];
    }

    /**
     * Find a role named "IT" (case insensitive) and return mention code.
     */
    public static function getItRoleMention(): string
    {
        $roles = self::getRoles();
        foreach ($roles as $role) {
            if (strcasecmp($role['name'] ?? '', 'IT') === 0) {
                return "<@&{$role['id']}>";
            }
        }

        return '@IT';
    }

    /**
     * Send an embedded message to a specific Discord channel.
     *
     * @param  array<string, mixed>  $embed
     */
    public static function sendEmbedMessage(string $channelId, array $embed, ?string $content = null): bool
    {
        $setting = self::getSetting();
        if (! $setting || ! $setting->bot_token) {
            return false;
        }

        try {
            $payload = [
                'embeds' => [$embed],
            ];
            if ($content !== null) {
                $payload['content'] = $content;
            }

            $response = Http::withoutVerifying()
                ->withToken($setting->bot_token, 'Bot')
                ->post("https://discord.com/api/v10/channels/{$channelId}/messages", $payload);

            if ($response->successful()) {
                return true;
            }

            Log::error('Discord sendEmbedMessage API failed: '.$response->body());
        } catch (\Exception $e) {
            Log::error('Discord sendEmbedMessage exception: '.$e->getMessage());
        }

        return false;
    }

    /**
     * Get channels grouped by category for a specific DiscordSetting instance (Cached).
     *
     * @return array<string, array<string, string>>
     */
    public static function getChannelsGroupedByCategoryForSetting(int|string $settingId): array
    {
        return Cache::remember("discord_channels_grouped_setting_{$settingId}", now()->addMinutes(10), function () use ($settingId) {
            $setting = DiscordSetting::find($settingId);
            if (! $setting || ! $setting->bot_token || ! $setting->guild_id) {
                return [];
            }

            try {
                $response = Http::withoutVerifying()
                    ->withToken($setting->bot_token, 'Bot')
                    ->get("https://discord.com/api/v10/guilds/{$setting->guild_id}/channels");

                if (! $response->successful()) {
                    Log::error('Discord getChannels API failed: '.$response->body());

                    return [];
                }

                $channels = $response->json();
                $categories = [];
                $textChannels = [];

                foreach ($channels as $channel) {
                    if (($channel['type'] ?? null) === 4) {
                        $categories[$channel['id']] = $channel['name'] ?? 'Unnamed Category';
                    } elseif (in_array(($channel['type'] ?? null), [0, 5], true)) {
                        $textChannels[] = $channel;
                    }
                }

                $grouped = [];
                $uncategorized = [];

                foreach ($textChannels as $channel) {
                    $parentId = $channel['parent_id'] ?? null;
                    $isAnnouncement = ($channel['type'] ?? null) === 5;
                    $channelPrefix = $isAnnouncement ? '📢 #' : '#';
                    $channelName = $channelPrefix.($channel['name'] ?? 'unnamed');

                    if ($parentId && isset($categories[$parentId])) {
                        $grouped[$categories[$parentId]][$channel['id']] = $channelName;
                    } else {
                        $uncategorized[$channel['id']] = $channelName;
                    }
                }

                if (! empty($uncategorized)) {
                    $grouped['Text Channels'] = $uncategorized;
                }

                return $grouped;
            } catch (\Exception $e) {
                Log::error('Discord getChannelsGroupedByCategoryForSetting exception: '.$e->getMessage());
            }

            return [];
        });
    }

    /**
     * Get server roles for a specific DiscordSetting instance (Cached).
     *
     * @return array<string, string>
     */
    public static function getRolesForSetting(int|string|DiscordSetting $setting): array
    {
        $settingObj = $setting instanceof DiscordSetting ? $setting : DiscordSetting::find($setting);
        if (! $settingObj || ! $settingObj->bot_token || ! $settingObj->guild_id) {
            return [];
        }

        $settingId = $settingObj->id;

        return Cache::remember("discord_roles_map_setting_{$settingId}", now()->addMinutes(10), function () use ($settingObj) {
            try {
                $response = Http::withoutVerifying()
                    ->withToken($settingObj->bot_token, 'Bot')
                    ->get("https://discord.com/api/v10/guilds/{$settingObj->guild_id}/roles");

                if ($response->successful()) {
                    $roles = [];
                    foreach ($response->json() as $role) {
                        if (($role['name'] ?? '') !== '@everyone') {
                            $roles[$role['id']] = '@'.($role['name'] ?? 'Unnamed Role');
                        }
                    }

                    return $roles;
                }
            } catch (\Exception $e) {
                Log::error('Discord getRolesForSetting exception: '.$e->getMessage());
            }

            return [];
        });
    }

    /**
     * Get role IDs matching target role names (e.g. ['IT', 'HR']) for pre-selection.
     *
     * @param  array<int, string>  $targetRoleNames
     * @return array<int, string>
     */
    public static function getDefaultRoleIdsForSetting(int|string|DiscordSetting $setting, array $targetRoleNames = ['IT', 'HR']): array
    {
        $roles = self::getRolesForSetting($setting);
        if (empty($roles)) {
            return [];
        }

        $matchedIds = [];
        foreach ($roles as $roleId => $roleName) {
            // strip leading '@' if present
            $cleanName = ltrim($roleName, '@');
            foreach ($targetRoleNames as $target) {
                if (strcasecmp($cleanName, $target) === 0) {
                    $matchedIds[] = (string) $roleId;
                }
            }
        }

        return array_unique($matchedIds);
    }

    /**
     * Find the IT role mention for a specific DiscordSetting instance.
     */
    public static function getItRoleMentionForSetting(DiscordSetting $setting): string
    {
        $roles = self::getRolesForSetting($setting);
        foreach ($roles as $roleId => $roleName) {
            $cleanName = ltrim($roleName, '@');
            if (strcasecmp($cleanName, 'IT') === 0) {
                return "<@&{$roleId}>";
            }
        }

        return '@IT';
    }

    /**
     * Send an embedded message using a specific DiscordSetting instance.
     *
     * @param  array<string, mixed>  $embed
     */
    public static function sendEmbedMessageForSetting(DiscordSetting $setting, string $channelId, array $embed, ?string $content = null): bool
    {
        if (! $setting->bot_token) {
            return false;
        }

        try {
            $payload = ['embeds' => [$embed]];
            if ($content !== null) {
                $payload['content'] = $content;
            }

            $response = Http::withoutVerifying()
                ->withToken($setting->bot_token, 'Bot')
                ->post("https://discord.com/api/v10/channels/{$channelId}/messages", $payload);

            if ($response->successful()) {
                return true;
            }

            Log::error('Discord sendEmbedMessageForSetting API failed: '.$response->body());
        } catch (\Exception $e) {
            Log::error('Discord sendEmbedMessageForSetting exception: '.$e->getMessage());
        }

        return false;
    }

    /**
     * Clear all cached Discord API responses for a setting.
     */
    public static function clearCacheForSetting(int|string $settingId): void
    {
        Cache::forget("discord_guild_info_{$settingId}");
        Cache::forget("discord_channels_raw_{$settingId}");
        Cache::forget("discord_roles_raw_{$settingId}");
        Cache::forget("discord_channels_grouped_setting_{$settingId}");
        Cache::forget("discord_roles_map_setting_{$settingId}");
    }
}
