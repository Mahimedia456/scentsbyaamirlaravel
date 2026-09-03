<?php

use App\Http\Controllers\Storefront\UblPaymentController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/payments/ubl/start/{token}', [UblPaymentController::class, 'start'])
    ->middleware('throttle:20,1')
    ->name('payments.ubl.start');

Route::match(['GET', 'POST'], '/payments/ubl/return/{token}', [UblPaymentController::class, 'returned'])
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->middleware('throttle:30,1')
    ->name('payments.ubl.return');

Route::post('/payments/ubl/retry/{token}', [UblPaymentController::class, 'retry'])
    ->middleware('throttle:10,1')
    ->name('payments.ubl.retry');
