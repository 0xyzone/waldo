<?php

namespace App\Services;

use App\Models\DiscordSetting;
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
            $response = Http::withToken($setting->bot_token, 'Bot')
                ->get("https://discord.com/api/v10/guilds/{$setting->guild_id}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Discord getGuildInfo API failed: '.$response->body());
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
            $response = Http::withToken($setting->bot_token, 'Bot')
                ->get("https://discord.com/api/v10/guilds/{$setting->guild_id}/channels");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Discord getChannels API failed: '.$response->body());
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
            // type 4 represents GUILD_CATEGORY
            if (($channel['type'] ?? null) === 4) {
                $categories[$channel['id']] = $channel['name'] ?? 'Unnamed Category';
            } elseif (($channel['type'] ?? null) === 0) { // type 0 represents GUILD_TEXT
                $textChannels[] = $channel;
            }
        }

        $grouped = [];
        $uncategorized = [];

        foreach ($textChannels as $channel) {
            $parentId = $channel['parent_id'] ?? null;
            $channelName = '#'.($channel['name'] ?? 'unnamed');

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
            $response = Http::withToken($setting->bot_token, 'Bot')
                ->get("https://discord.com/api/v10/guilds/{$setting->guild_id}/roles");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Discord getRoles API failed: '.$response->body());
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

            $response = Http::withToken($setting->bot_token, 'Bot')
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
}
