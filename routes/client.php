<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\DeviceClientController;
use App\Http\Controllers\InvoiceClientController;
use App\Http\Controllers\RackClientController;
use App\Http\Controllers\ServiceClientController;
use App\Http\Controllers\TicketClientController;

Route::middleware(['auth', 'verified', 'role:client'])->group(function () {
    Route::GET('client/dashboard', [DashboardAdminController::class, 'client'])->name('client.dashboard');

    Route::resource('client/racks', RackClientController::class);
    Route::resource('client/devices', DeviceClientController::class);
    Route::resource('client/services', ServiceClientController::class);
    Route::resource('client/invoices', InvoiceClientController::class);
    Route::get('/client/invoices/{invoice}/payment', [InvoiceClientController::class, 'paymentInvoice'])->name('invoices.payment.client');
    Route::post('/client/invoices/{invoice}/payment', [InvoiceClientController::class, 'storePayment'])->name('client.invoices.payment.store');
    Route::get(
        '/client/invoices/{invoice}/download',
        [InvoiceClientController::class, 'downloadInvoice']
    )->name('admin.invoices.download');

    Route::resource('client/tickets', TicketClientController::class);
    Route::post('client/tickets/{ticketId}/reply', [TicketClientController::class, 'storeReply'])->name('client.tickets.reply');
});
