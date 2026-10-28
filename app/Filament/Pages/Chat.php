<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

/**
 * @deprecated Legacy chat page retired in v2.8.3. Maintained as a silent redirect stub
 * to prevent fatal errors when cached components exist on remote environments.
 */
class Chat extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'chat';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(): void
    {
        redirect('/kamkaj');
    }
}
