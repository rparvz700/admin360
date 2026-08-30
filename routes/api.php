<?php

use App\Http\Controllers\API\TicketController;
use Illuminate\Support\Facades\Route;

Route::prefix('vehicle-support')
    ->middleware('static.bearer')
    ->group(function () {
        Route::post('/tickets', [TicketController::class, 'index']);
        Route::post('/tickets/create', [TicketController::class, 'store']);
        Route::get('/tickets/dropdowns', [TicketController::class, 'getDropdowns']);
        Route::get('/tickets/{ticketId}', [TicketController::class, 'show']);
        Route::post('/vehicle-assignments/{vehicleAssignment}/location-tracking', [TicketController::class, 'storeLocationTracking']);
        Route::post('/vehicle-assignments/{vehicleAssignment}/start', [TicketController::class, 'startTrip']);
        Route::post('/vehicle-assignments/{vehicleAssignment}/complete', [TicketController::class, 'completeTrip']);
    });
