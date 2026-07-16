<?php

namespace Tests\Feature;

use App\Filament\Resources\DiscordSettingResource\DiscordSettingResource;
use App\Models\DiscordSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DiscordSettingResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_access_discord_setting_index_page(): void
    {
        $role = Role::create(['name' => 'HR']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $this->actingAs($user);

        DiscordSetting::create([
            'bot_token' => 'dummy-token',
            'guild_id' => 'dummy-guild',
            'target_channel_id' => 'dummy-channel',
        ]);

        $response = $this->get(DiscordSettingResource::getUrl('index'));
        $response->assertStatus(200);
    }

    public function test_can_access_discord_setting_create_page(): void
    {
        $role = Role::create(['name' => 'HR']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $this->actingAs($user);

        $response = $this->get(DiscordSettingResource::getUrl('create'));
        $response->assertStatus(200);
    }
}
