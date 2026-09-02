<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\DashboardAdminController;


Route::middleware(['auth', 'verified', 'role:marketing'])->group(function () {
    Route::GET('marketing/dashboard', [DashboardAdminController::class, 'marketing'])->name('marketing.dashboard');
});
