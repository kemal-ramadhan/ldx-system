<?php

use App\Models\Service;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Issue invoices for active services 7 days before their next due date
Schedule::command(
    'invoices:generate'
)->dailyAt('08:00')->withoutOverlapping();

// H-7 / due date reminders, mark & notify overdue invoices
Schedule::command(
    'invoices:reminders'
)->dailyAt('08:30')->withoutOverlapping();

// Fetch telemetry every 2 minutes and prune records older than 7 days
Schedule::command('telemetry:fetch')
    ->everyTwoMinutes()
    ->withoutOverlapping();