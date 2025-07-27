<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController; // Tambahkan ini
use App\Http\Controllers\McController; // Tambahkan ini

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// --- API Endpoints Baru ---

// API untuk mendapatkan Snap Token
// Middleware 'auth:sanctum' biasanya untuk API, tapi karena ini di web, kita biarkan saja.
// Pastikan ini diakses oleh user yang login.
Route::get('/payment/snap-token/{booking}', [PaymentController::class, 'getSnapToken'])->middleware('auth')->name('api.snap-token');

// API untuk Midtrans Notification (Webhook)
Route::post('/midtrans/notification', [PaymentController::class, 'handleNotification'])->name('midtrans.notification');

// API untuk mendapatkan jadwal MC (digunakan oleh FullCalendar)
Route::get('/mc/{mc}/schedules', [McController::class, 'getSchedules'])->name('api.mc.schedules');