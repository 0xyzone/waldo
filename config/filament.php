<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Broadcasting
    |--------------------------------------------------------------------------
    |
    | Connect Filament to Laravel Reverb WebSockets server.
    | This allows real-time chat and notifications throughout the panel.
    |
    */

    'broadcasting' => [
        'echo' => [
            'broadcaster' => 'reverb',
            'key' => env('VITE_REVERB_APP_KEY', env('REVERB_APP_KEY')),
            'wsHost' => env('VITE_REVERB_HOST', env('REVERB_HOST', 'localhost')),
            'wsPort' => env('VITE_REVERB_PORT', env('REVERB_PORT', 8080)),
            'wssPort' => env('VITE_REVERB_PORT', env('REVERB_PORT', 8080)),
            'forceTLS' => (env('VITE_REVERB_SCHEME', env('REVERB_SCHEME', 'http')) === 'https'),
            'enabledTransports' => ['ws', 'wss'],
        ],
    ],

    'default_filesystem_disk' => env('FILESYSTEM_DISK', 'local'),

    'temporary_file_url_expiry_minutes' => 30,

    'assets_path' => null,

    'cache_path' => base_path('bootstrap/cache/filament'),

    'livewire_loading_delay' => 'default',

    'file_generation' => [
        'flags' => [],
    ],

    'system_route_prefix' => 'filament',

];
