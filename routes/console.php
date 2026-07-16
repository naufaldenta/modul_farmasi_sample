<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('farmasi:evaluate')->dailyAt('00:01');
Schedule::command('inventory:evaluate-expiry-alerts')->dailyAt('00:05');
Schedule::command('farmasi:forecast')->monthlyOn(1, '00:10');
