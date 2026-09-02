<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\DashboardAdminController;


Route::middleware(['auth', 'verified', 'role:teknisi'])->group(function () {
    Route::GET('teknisi/dashboard', [DashboardAdminController::class, 'teknisi'])->name('teknisi.dashboard');
});
