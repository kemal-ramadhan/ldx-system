<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\VisitCheckinController;
use App\Http\Controllers\VisitorController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/buku-tamu', [VisitorController::class, 'guestChoice'])->name('buku.tamu');
Route::get('/daftar-buku-tamu', [VisitorController::class, 'guest'])->name('daftar.buku.tamu');
Route::post('/create-buku-tamu', [VisitorController::class, 'storeGuest'])->middleware('throttle:20,1')->name('create.buku.tamu');

// photo step: only through a signed URL issued right after check-in (no guessing visitor ids)
Route::get('/daftar-buku-tamu-lampiran/{id}', [VisitorController::class, 'guestLampiran'])->middleware('signed')->name('daftar.buku.tamu.lampiran');
Route::post('/create-buku-tamu-lampiran/{id}', [VisitorController::class, 'storeLampiran'])->middleware(['signed', 'throttle:20,1'])->name('buku.tamu.lampiran.store');

// self check-in for an approved visit request (pengajuan kunjungan)
Route::prefix('kunjungan/checkin')->name('kunjungan.checkin')->group(function () {
    Route::get('/', [VisitCheckinController::class, 'form']);
    Route::post('/', [VisitCheckinController::class, 'verify'])->middleware('throttle:10,1')->name('.verify');
    Route::get('/tamu', [VisitCheckinController::class, 'guests'])->name('.guests');
    Route::post('/tamu/{member}', [VisitCheckinController::class, 'identify'])->middleware('throttle:10,1')->name('.identify');
    Route::get('/konfirmasi', [VisitCheckinController::class, 'confirm'])->name('.confirm');
    Route::post('/konfirmasi', [VisitCheckinController::class, 'store'])->middleware('throttle:10,1')->name('.store');
});


require __DIR__.'/admin.php';
require __DIR__.'/client.php';
require __DIR__.'/marketing.php';
require __DIR__.'/teknisi.php';
require __DIR__.'/settings.php';
