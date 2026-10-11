<?php

use App\Models\Service;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(
    'invoices:generate'
)->dailyAt('17:52');

Schedule::command(
    'invoices:reminders'
)->dailyAt('17:52');

// Fetch telemetry every 2 minutes and prune records older than 7 days
Schedule::command('telemetry:fetch')
    ->everyTwoMinutes()
    ->withoutOverlapping();