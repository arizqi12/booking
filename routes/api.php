<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\McController; // Ini harus diimpor

// Simple test route
Route::get('/test-api', function() {
    return response()->json(['message' => 'API is working!']);
});

// Ini adalah rute bawaan dari Laravel/Sanctum, biarkan saja
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API untuk mendapatkan semua layanan MC aktif
// Tidak memerlukan autentikasi 'auth'
Route::get('/mc/services', [McController::class, 'getServices'])->name('api.mc.services');

// API untuk mendapatkan jadwal MC (digunakan oleh FullCalendar)
// Parameter {mc} akan otomatis dibinding ke objek App\Models\Mc
Route::get('/mc/{mc}/schedules', [McController::class, 'getSchedules'])->name('api.mc.schedules');

// API untuk mendapatkan Snap Token (membutuhkan user login)
Route::get('/payment/snap-token/{booking}', [PaymentController::class, 'getSnapToken'])->middleware('auth')->name('api.snap-token');

// API untuk Midtrans Notification (Webhook)
Route::post('/midtrans/notification', [PaymentController::class, 'handleNotification'])->name('midtrans.notification');