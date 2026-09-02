<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\VisitorController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/buku-tamu', [VisitorController::class, 'guestChoice'])->name('buku.tamu');
Route::get('/daftar-buku-tamu', [VisitorController::class, 'guest'])->name('daftar.buku.tamu');
Route::post('/create-buku-tamu', [VisitorController::class, 'storeGuest'])->name('create.buku.tamu');
Route::get('/daftar-buku-tamu-lampiran/{id}', [VisitorController::class, 'guestLampiran'])->name('daftar.buku.tamu.lampiran');
Route::post('/create-buku-tamu-lampiran/{id}', [VisitorController::class, 'storeLampiran'])->name('buku.tamu.lampiran.store');


require __DIR__.'/admin.php';
require __DIR__.'/client.php';
require __DIR__.'/marketing.php';
require __DIR__.'/teknisi.php';
require __DIR__.'/settings.php';
