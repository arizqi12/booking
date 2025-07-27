<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\McController; // Tambahkan ini
use App\Http\Controllers\BookingController; // Tambahkan ini
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\McScheduleController; // Tambahkan ini

// Rute bawaan Laravel Breeze
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// --- Rute Baru Untuk Aplikasi Booking MC ---

// Rute untuk Halaman Publik MC (Detail & Booking)
Route::get('/mc/{id}', [McController::class, 'show'])->name('mc.show');
Route::post('/mc/{id}/book', [BookingController::class, 'store'])->name('booking.store')->middleware('auth'); // Hanya user terautentikasi bisa booking

// Rute untuk Dashboard User (Pemesan)
Route::middleware(['auth', 'role:user'])->group(function () { // Middleware role akan kita buat nanti
    Route::get('/my-bookings', [BookingController::class, 'index'])->name('my.bookings.index');
    Route::get('/my-bookings/{id}', [BookingController::class, 'show'])->name('my.bookings.show');
    // Tambahkan rute untuk pembayaran sisa, pembatalan, review
});

// Rute untuk Dashboard Admin (MC)
Route::middleware(['auth', 'role:admin'])->group(function () { // Middleware role akan kita buat nanti
    Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('/admin/bookings', [BookingController::class, 'adminIndex'])->name('admin.bookings.index');
    Route::get('/admin/bookings/{id}', [BookingController::class, 'adminShow'])->name('admin.bookings.show');
    Route::post('/admin/bookings/{id}/confirm', [BookingController::class, 'confirm'])->name('admin.bookings.confirm');
    Route::post('/admin/bookings/{id}/reject', [BookingController::class, 'reject'])->name('admin.bookings.reject');
    // Rute untuk kelola jadwal, portofolio, dll.
});

// Rute untuk Dashboard Editor (Kamu)
Route::middleware(['auth', 'role:editor'])->group(function () { // Middleware role akan kita buat nanti
    Route::get('/editor/dashboard', [DashboardController::class, 'editorDashboard'])->name('editor.dashboard');
    // Rute untuk manajemen user, konten, dll.
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('/admin/bookings', [BookingController::class, 'adminIndex'])->name('admin.bookings.index');
    Route::get('/admin/bookings/{id}', [BookingController::class, 'adminShow'])->name('admin.bookings.show');
    Route::post('/admin/bookings/{id}/confirm', [BookingController::class, 'confirm'])->name('admin.bookings.confirm');
    Route::post('/admin/bookings/{id}/reject', [BookingController::class, 'reject'])->name('admin.bookings.reject');

    // --- Rute Baru Untuk Manajemen Jadwal MC ---
    Route::get('/admin/schedules', [McScheduleController::class, 'index'])->name('admin.schedules.index');
    Route::post('/admin/schedules', [McScheduleController::class, 'store'])->name('admin.schedules.store');
    Route::delete('/admin/schedules/{id}', [McScheduleController::class, 'destroy'])->name('admin.schedules.destroy');
});