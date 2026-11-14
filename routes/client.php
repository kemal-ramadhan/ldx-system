<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\DeviceClientController;
use App\Http\Controllers\InvoiceClientController;
use App\Http\Controllers\RackClientController;
use App\Http\Controllers\ServiceClientController;
use App\Http\Controllers\TicketClientController;
use App\Http\Controllers\CrossConnectClientController;
use App\Http\Controllers\InterconnectionClientController;
use App\Http\Controllers\VisitRequestClientController;

Route::middleware(['auth', 'verified', 'role:client'])->group(function () {
    Route::GET('client/dashboard', [DashboardAdminController::class, 'client'])->name('client.dashboard');

    Route::resource('client/racks', RackClientController::class)->names('client.racks');
    Route::get('/client/racks-monitoring/data', [App\Http\Controllers\RackMonitoringController::class, 'data'])->name('client.racks.monitoring.data');
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
    Route::get('client/tickets/{ticketId}/chat-data', [TicketClientController::class, 'getChatData'])->name('client.tickets.chat-data');
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
            '/interconnections/{id}',
            [InterconnectionClientController::class, 'show']
        )->name('client.interconnections.show');

        // Cross connects touching the client's ports (read-only + terminate request)
        Route::get(
            '/cross-connects',
            [CrossConnectClientController::class, 'index']
        )->name('client.cross-connects.index');

        Route::get(
            '/cross-connects/{crossConnect}',
            [CrossConnectClientController::class, 'show']
        )->name('client.cross-connects.show');

        Route::post(
            '/cross-connects/{crossConnect}/terminate-request',
            [CrossConnectClientController::class, 'requestTerminate']
        )->name('client.cross-connects.terminate-request');

        // pengajuan kunjungan
        Route::get('/visit-requests', [VisitRequestClientController::class, 'index'])->name('client.visit-requests.index');
        Route::get('/visit-requests/create', [VisitRequestClientController::class, 'create'])->name('client.visit-requests.create');
        Route::post('/visit-requests', [VisitRequestClientController::class, 'store'])->name('client.visit-requests.store');
        Route::get('/visit-requests/{id}', [VisitRequestClientController::class, 'show'])->name('client.visit-requests.show');
        Route::get('/visit-requests/{id}/edit', [VisitRequestClientController::class, 'edit'])->name('client.visit-requests.edit');
        Route::put('/visit-requests/{id}', [VisitRequestClientController::class, 'update'])->name('client.visit-requests.update');
        Route::patch('/visit-requests/{id}/cancel', [VisitRequestClientController::class, 'cancel'])->name('client.visit-requests.cancel');
    });
});
