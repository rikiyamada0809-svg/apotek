<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('expired:check')->dailyAt('06:00');
Schedule::command('stock:check-reorder')->dailyAt('07:00');
Schedule::command('stock:abc-analysis')->weeklyOn(1, '01:00');
