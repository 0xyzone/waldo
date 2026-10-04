<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\KamkajPanelProvider;
use NotificationChannels\WebPush\WebPushServiceProvider;

return [
    AppServiceProvider::class,
    KamkajPanelProvider::class,
    WebPushServiceProvider::class,
];
