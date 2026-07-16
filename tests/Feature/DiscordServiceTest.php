<?php

namespace Tests\Feature;

use App\Models\DiscordSetting;
use App\Services\DiscordService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DiscordServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_setting_returns_null_when_no_setting(): void
    {
        $this->assertNull(DiscordService::getSetting());
    }

    public function test_get_setting_returns_stored_setting(): void
    {
        $setting = DiscordSetting::create([
            'bot_token' => 'dummy-token',
            'guild_id' => 'dummy-guild',
            'target_channel_id' => 'dummy-channel',
        ]);

        $retrieved = DiscordService::getSetting();
        $this->assertNotNull($retrieved);
        $this->assertEquals($setting->id, $retrieved->id);
    }

    public function test_get_guild_info_calls_discord_api(): void
    {
        DiscordSetting::create([
            'bot_token' => 'my-token',
            'guild_id' => 'my-guild',
            'target_channel_id' => 'my-channel',
        ]);

        Http::fake([
            'https://discord.com/api/v10/guilds/my-guild' => Http::response([
                'id' => 'my-guild',
                'name' => 'My Test Server',
                'icon' => 'iconhash',
            ], 200),
        ]);

        $info = DiscordService::getGuildInfo();

        $this->assertNotNull($info);
        $this->assertEquals('My Test Server', $info['name']);

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bot my-token')
                && $request->url() === 'https://discord.com/api/v10/guilds/my-guild';
        });
    }

    public function test_get_channels_grouped_by_category(): void
    {
        DiscordSetting::create([
            'bot_token' => 'my-token',
            'guild_id' => 'my-guild',
            'target_channel_id' => 'my-channel',
        ]);

        Http::fake([
            'https://discord.com/api/v10/guilds/my-guild/channels' => Http::response([
                [
                    'id' => 'cat-1',
                    'name' => 'General Category',
                    'type' => 4,
                ],
                [
                    'id' => 'chan-1',
                    'name' => 'welcome',
                    'type' => 0,
                    'parent_id' => 'cat-1',
                ],
                [
                    'id' => 'chan-2',
                    'name' => 'general',
                    'type' => 0,
                    'parent_id' => 'cat-1',
                ],
                [
                    'id' => 'chan-3',
                    'name' => 'uncategorized-text',
                    'type' => 0,
                    'parent_id' => null,
                ],
            ], 200),
        ]);

        $grouped = DiscordService::getChannelsGroupedByCategory();

        $this->assertCount(2, $grouped);
        $this->assertArrayHasKey('General Category', $grouped);
        $this->assertArrayHasKey('Text Channels', $grouped);

        $this->assertEquals('#welcome', $grouped['General Category']['chan-1']);
        $this->assertEquals('#general', $grouped['General Category']['chan-2']);
        $this->assertEquals('#uncategorized-text', $grouped['Text Channels']['chan-3']);
    }

    public function test_get_it_role_mention(): void
    {
        DiscordSetting::create([
            'bot_token' => 'my-token',
            'guild_id' => 'my-guild',
            'target_channel_id' => 'my-channel',
        ]);

        Http::fake([
            'https://discord.com/api/v10/guilds/my-guild/roles' => Http::response([
                [
                    'id' => 'role-123',
                    'name' => 'IT',
                ],
                [
                    'id' => 'role-456',
                    'name' => 'Admin',
                ],
            ], 200),
        ]);

        $mention = DiscordService::getItRoleMention();
        $this->assertEquals('<@&role-123>', $mention);
    }

    public function test_send_embed_message(): void
    {
        DiscordSetting::create([
            'bot_token' => 'my-token',
            'guild_id' => 'my-guild',
            'target_channel_id' => 'my-channel',
        ]);

        Http::fake([
            'https://discord.com/api/v10/channels/chan-999/messages' => Http::response([], 200),
        ]);

        $success = DiscordService::sendEmbedMessage('chan-999', ['title' => 'Hello'], 'Pinging IT');

        $this->assertTrue($success);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://discord.com/api/v10/channels/chan-999/messages'
                && $request['content'] === 'Pinging IT'
                && $request['embeds'][0]['title'] === 'Hello';
        });
    }
}
