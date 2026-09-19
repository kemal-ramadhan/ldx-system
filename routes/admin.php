<?php

use App\Http\Controllers\CategoriesController;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RackController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TicketCategoryController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketPriorityController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\InterconnectionRequestController;
use App\Http\Controllers\CrossConnectController;
use App\Http\Controllers\DevicePortController;
use App\Models\Invoice;
use App\Models\TicketPriority;
use App\Models\Visitor;

Route::middleware(['auth', 'verified', 'role:super-admin'])->group(function () {
    Route::GET('admin/dashboard', [DashboardAdminController::class, 'superAdmin'])->name('admin.dashboard');

    // user management
    Route::resource('admin/users', UserController::class);
    Route::GET('admin/users/{userId}/client', [UserController::class, 'createClient'])->name('admin.clients.create');
    Route::POST('admin/users/{userId}/client', [UserController::class, 'storeClient'])->name('admin.clients.store');

    // companies management
    Route::resource('admin/clients', ClientController::class);
    Route::post('/admin/clients/{client}/invite-member', [ClientController::class, 'inviteMember'])->name('clients.invite-member');
    Route::delete('/admin/clients/pic/{pic}', [ClientController::class, 'deleteMember'])->name('clients.delete-member');

    // racks management
    // locations management
    Route::resource('admin/locations', LocationController::class);

    // rooms management
    Route::resource('admin/rooms', RoomController::class);

    // visitor management
    Route::resource('admin/visitors', VisitorController::class);

    // racks management
    Route::delete('admin/racks/bulk-destroy', [RackController::class, 'bulkDestroy'])->name('admin.racks.bulk-destroy');
    Route::resource('admin/racks', RackController::class);
    Route::post('admin/racks/{rack}/owners', [RackController::class, 'assignOwner'])->name('admin.racks.assign-owner');
    Route::delete('admin/racks/{ownerId}/owner', [RackController::class, 'removeOwner'])->name('admin.racks.remove-owner');
    Route::put('admin/racks/owners/{id}', [RackController::class, 'updateOwner'])->name('admin.racks.update-owner');
    Route::post('admin/racks/{rack}/devices', [RackController::class, 'storeDevice']);
    Route::put('admin/racks/devices/{device}', [RackController::class, 'updateDevice'])->name('admin.racks.devices.update');
    Route::delete('admin/racks/devices/{id}', [RackController::class, 'destroyDevice'])->name('admin.racks.devices.destroy');

    // categories product
    Route::resource('admin/categories', CategoriesController::class);

    // product
    Route::resource('admin/products', ProductController::class);

    // Service
    Route::resource('admin/services', ServiceController::class);
    Route::patch('/admin/services/{service}/activate', [ServiceController::class, 'activate']);
    Route::patch('/admin/services/{service}/suspend', [ServiceController::class, 'suspend']);
    Route::patch('/admin/services/{service}/terminate', [ServiceController::class, 'terminate']);
    Route::post('/admin/services/{service}/generate-invoice', [ServiceController::class, 'generateInvoice']);

    // invoice
    Route::resource('admin/invoices', InvoiceController::class);
    Route::post('/admin/invoices/{invoice}/send', [InvoiceController::class, 'sendInvoice'])->name('invoices.send');
    Route::get('/admin/invoices/{invoice}/payment', [InvoiceController::class, 'paymentInvoice'])->name('invoices.payment');
    Route::post('/admin/invoices/{invoice}/paymentbyadmin', [InvoiceController::class, 'storePayment'])->name('admin.invoices.payment.store');
    Route::patch('/admin/invoices/{invoice}/verify', [InvoiceController::class, 'verifyPayment'])->name('admin.invoices.payment.verify');
    /**
     * =========================================
     * REJECT PAYMENT
     * =========================================
     */
    Route::patch(
        '/admin/invoices/{invoice}/reject-payment',
        [InvoiceController::class, 'rejectPayment']
    )->name('admin.invoices.reject-payment');

    Route::get(
        '/admin/invoices/{invoice}/download',
        [InvoiceController::class, 'downloadInvoice']
    )->name('admin.invoices.download');

    Route::resource('admin/tickets', TicketController::class);
    Route::post('admin/tickets/{ticketId}/reply', [TicketController::class, 'reply'])->name('admin.tickets.reply');
    Route::put('admin/tickets/{id}/resolve', [TicketController::class, 'resolve'])->name('admin.tickets.resolve');
    Route::put('admin/tickets/{id}/close', [TicketController::class, 'close'])->name('admin.tickets.close');
    Route::put('admin/tickets/{id}/reopen', [TicketController::class, 'reopen'])->name('admin.tickets.reopen');


    Route::resource('admin/tickets-category', TicketCategoryController::class);
    Route::patch(
        '/admin/tickets-category/{ticketCategory}/toggle-active',
        [TicketCategoryController::class, 'toggleActive']
    );
    Route::resource('admin/tickets-priority', TicketPriorityController::class);
    Route::patch(
        '/admin/tickets-priority/{ticketPriority}/toggle-active',
        [TicketPriorityController::class, 'toggleActive']
    );

    Route::prefix('admin')->group(function () {
        // Device Ports
        Route::post(
            'racks/devices/{device}/ports',
            [RackController::class, 'storeDevicePort']
        )->name('admin.racks.devices.ports.store');

        Route::post(
            'racks/devices/{device}/ports/generate',
            [RackController::class, 'generateDevicePorts']
        )->name('admin.racks.devices.ports.generate');

        Route::put(
            'racks/devices/ports/{port}',
            [RackController::class, 'updateDevicePort']
        )->name('admin.racks.devices.ports.update');

        Route::delete(
            'racks/devices/ports/{port}',
            [RackController::class, 'destroyDevicePort']
        )->name('admin.racks.devices.ports.destroy');

        Route::get(
            'interconnections',
            [InterconnectionRequestController::class, 'index']
        )->name('admin.interconnections.index');

        Route::get(
            'interconnections/create',
            [InterconnectionRequestController::class, 'create']
        )->name('admin.interconnections.create');

        Route::post(
            'interconnections',
            [InterconnectionRequestController::class, 'store']
        )->name('admin.interconnections.store');

        Route::get(
            'interconnections/{interconnection}',
            [InterconnectionRequestController::class, 'show']
        )->name('admin.interconnections.show');

        Route::patch(
            'interconnections/{interconnection}/approve-destination',
            [InterconnectionRequestController::class, 'approveDestination']
        )->name(
            'admin.interconnections.approve-destination'
        );

        Route::patch(
            'interconnections/{interconnection}/reject-destination',
            [InterconnectionRequestController::class, 'rejectDestination']
        )->name(
            'admin.interconnections.reject-destination'
        );

        Route::patch(
            'interconnections/{interconnection}/approve-dc',
            [InterconnectionRequestController::class, 'approveDc']
        )->name(
            'admin.interconnections.approve-dc'
        );

        Route::patch(
            'interconnections/{interconnection}/reject-dc',
            [InterconnectionRequestController::class, 'rejectDc']
        )->name(
            'admin.interconnections.reject-dc'
        );

        Route::patch(
            'interconnections/{interconnection}/start-installation',
            [InterconnectionRequestController::class, 'startInstallation']
        )->name(
            'admin.interconnections.start-installation'
        );

        Route::patch(
            'interconnections/{interconnection}/start-testing',
            [InterconnectionRequestController::class, 'startTesting']
        )->name(
            'admin.interconnections.start-testing'
        );

        Route::patch(
            'interconnections/{interconnection}/complete',
            [InterconnectionRequestController::class, 'complete']
        )->name(
            'admin.interconnections.complete'
        );

        Route::patch(
            'interconnections/{interconnection}/cancel',
            [InterconnectionRequestController::class, 'cancel']
        )->name(
            'admin.interconnections.cancel'
        );

        Route::get(
            'cross-connects',
            [CrossConnectController::class, 'index']
        )->name('admin.cross-connects.index');

        Route::get(
            'cross-connects/{crossConnect}',
            [CrossConnectController::class, 'show']
        )->name('admin.cross-connects.show');

        Route::patch(
            'cross-connects/{crossConnect}/terminate',
            [CrossConnectController::class, 'terminate']
        )->name('admin.cross-connects.terminate');

        Route::get(
            'device-ports/{devicePort}',
            [DevicePortController::class, 'show']
        )->name('admin.device-ports.show');
    });
});
