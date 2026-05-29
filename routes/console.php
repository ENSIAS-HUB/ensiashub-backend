<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sync Azure Drive → DB toutes les nuits à 2h du matin
Schedule::command('drive:sync-azure')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground();
