<?php

namespace App\Filament\Pages;

use Agroezinger\FilamentShieldEnhanced\Traits\HasPageShield;
use App\Services\ChangelogService;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class Changelog extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static string|UnitEnum|null $navigationGroup = 'System Settings';

    protected static ?string $navigationLabel = 'Changelog';

    protected static ?string $title = 'System Changelog';

    protected static ?string $slug = 'changelog';

    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.pages.changelog';

    public static function getShieldPagePermissions(): array
    {
        return [
            'view' => 'View Changelog',
        ];
    }

    public function getHeading(): string
    {
        return 'System Changelog & Release Notes';
    }

    public function getSubheading(): ?string
    {
        return 'Detailed release history, feature milestones, and system updates for the Waldo Portal.';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getReleases(): array
    {
        return ChangelogService::getReleases();
    }

    public function getCurrentVersion(): string
    {
        return ChangelogService::getCurrentVersion();
    }
}
