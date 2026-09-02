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
)->dailyAt('11:27');

Schedule::command(
    'invoices:reminders'
)->dailyAt('11:27');

Service::where('status', 'active')
    ->whereDate(
        'next_due_date',
        '<=',
        now()
    );