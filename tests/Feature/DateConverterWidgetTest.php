<?php

namespace Tests\Feature;

use App\Filament\Widgets\DateConverterWidget;
use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DateConverterWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed shield roles & permissions
        $this->seed(ShieldSeeder::class);
    }

    public function test_widget_mounts_with_defaults(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->actingAs($user);

        $test = Livewire::test(DateConverterWidget::class)
            ->assertSet('activeTab', 'ad_to_bs');

        $this->assertNotEmpty($test->get('adDate'));
        $this->assertNotEmpty($test->get('bsYear'));
        $this->assertNotEmpty($test->get('bsMonth'));
        $this->assertNotEmpty($test->get('bsDay'));
    }

    public function test_can_convert_ad_to_bs(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);

        Livewire::test(DateConverterWidget::class)
            ->set('adDate', '2026-07-21')
            ->assertSet('convertedBsDate', '2083.04.05')
            ->assertSet('convertedBsDateNp', '२०८३ साउन ५')
            ->assertSet('convertedBsWeekday', 'मंगलबार');
    }

    public function test_ad_out_of_range_handling(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);

        Livewire::test(DateConverterWidget::class)
            ->set('adDate', '1930-01-01')
            ->assertSet('convertedBsDate', 'Out of supported range (1944 - 2033)')
            ->assertSet('convertedBsDateNp', null);
    }

    public function test_can_convert_bs_to_ad(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);

        Livewire::test(DateConverterWidget::class)
            ->set('activeTab', 'bs_to_ad')
            ->set('bsYear', '2083')
            ->set('bsMonth', '4')
            ->set('bsDay', '5')
            ->assertSet('convertedAdDate', '2026-07-21')
            ->assertSet('convertedAdWeekday', 'Tuesday');
    }

    public function test_invalid_bs_date_handling(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);

        Livewire::test(DateConverterWidget::class)
            ->set('activeTab', 'bs_to_ad')
            // Month 1 has 31 days in 2083 BS, so Day 32 is invalid.
            ->set('bsYear', '2083')
            ->set('bsMonth', '1')
            ->set('bsDay', '32')
            ->assertSet('convertedAdDate', 'Invalid BS Date')
            ->assertSet('convertedAdWeekday', null);
    }
}
