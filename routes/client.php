<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\DeviceClientController;
use App\Http\Controllers\InvoiceClientController;
use App\Http\Controllers\RackClientController;
use App\Http\Controllers\ServiceClientController;
use App\Http\Controllers\TicketClientController;
use App\Http\Controllers\InterconnectionClientController;

Route::middleware(['auth', 'verified', 'role:client'])->group(function () {
    Route::GET('client/dashboard', [DashboardAdminController::class, 'client'])->name('client.dashboard');

    Route::resource('client/racks', RackClientController::class)->names('client.racks');
    Route::resource('client/devices', DeviceClientController::class)->names('client.devices');
    Route::resource('client/services', ServiceClientController::class)->names('client.services');
    Route::resource('client/invoices', InvoiceClientController::class)->names('client.invoices');
    Route::get('/client/invoices/{invoice}/payment', [InvoiceClientController::class, 'paymentInvoice'])->name('invoices.payment.client');
    Route::post('/client/invoices/{invoice}/payment', [InvoiceClientController::class, 'storePayment'])->name('client.invoices.payment.store');
    Route::get(
        '/client/invoices/{invoice}/download',
        [InvoiceClientController::class, 'downloadInvoice']
    )->name('client.invoices.download');

    Route::resource('client/tickets', TicketClientController::class)->names('client.tickets');
    Route::post('client/tickets/{ticketId}/reply', [TicketClientController::class, 'storeReply'])->name('client.tickets.reply');

    Route::prefix('client')->group(function () {
        Route::get(
            '/interconnections',
            [InterconnectionClientController::class, 'index']
        )->name('interconnections.index');

        Route::get(
            '/interconnections/create',
            [InterconnectionClientController::class, 'create']
        )->name('interconnections.create');

        Route::post(
            '/interconnections',
            [InterconnectionClientController::class, 'store']
        )->name('interconnections.store');

        Route::get(
            '/interconnections/destination-clients/{clientId}/devices',
            [InterconnectionClientController::class, 'destinationDevices']
        )->name('interconnections.destination-devices');

        Route::get(
            '/interconnections/devices/{deviceId}/ports',
            [InterconnectionClientController::class, 'devicePorts']
        )->name('interconnections.device-ports');

        Route::get(
            '/interconnections/{id}',
            [InterconnectionClientController::class, 'show']
        )->name('client.interconnections.show');
    });
});
