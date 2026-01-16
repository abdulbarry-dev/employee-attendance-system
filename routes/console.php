<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule daily penalty calculations at midnight
Schedule::command('attendance:calculate-penalties')->dailyAt('00:30');

// Take Horizon snapshots for metrics
Schedule::command('horizon:snapshot')->everyFiveMinutes();
