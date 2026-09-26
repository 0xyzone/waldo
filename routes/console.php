<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('employees:sync')->hourly()->withoutOverlapping();
Schedule::command('biometrics:sync')->hourly()->withoutOverlapping();
Schedule::command('suspensions:check-status')->daily()->withoutOverlapping();
Schedule::command('transitions:apply-effective')->daily()->withoutOverlapping();
Schedule::command('sync-logs:prune')->daily()->withoutOverlapping();
