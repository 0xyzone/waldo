<?php

namespace Tests\Feature;

use App\Models\CustomFont;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FontUploadTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test custom font uploading works correctly with extension validation.
     */
    public function test_can_upload_custom_font(): void
    {
        Storage::fake('public');

        $role = Role::create(['name' => 'HR']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $this->actingAs($user);

        $response = $this->get(route('letters.fonts'));
        $response->assertStatus(200);

        $file = UploadedFile::fake()->create('Sugar Loved.ttf', 100);

        $response = $this->post(route('letters.fonts.store'), [
            'family_name' => 'Sugar Loved',
            'font_file' => $file,
            'style' => 'normal',
            'weight' => '400',
        ]);

        $response->assertRedirect(route('letters.fonts'));
        $response->assertSessionHas('success', 'Font uploaded successfully.');

        $this->assertDatabaseHas('custom_fonts', [
            'family_name' => 'Sugar Loved',
            'style' => 'normal',
            'weight' => '400',
            'original_name' => 'Sugar Loved.ttf',
        ]);

        $font = CustomFont::first();
        $this->assertNotNull($font);
        Storage::disk('public')->assertExists('letter-fonts/'.$font->file_name);
    }
}
