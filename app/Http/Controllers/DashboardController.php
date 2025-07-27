<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Redirect to specific dashboard based on user role.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isEditor()) {
            return redirect()->route('editor.dashboard');
        } elseif ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } else { // Default to user dashboard for 'user' role
            // Kita akan buat view ini nanti atau arahkan ke my.bookings.index
            return view('user.dashboard'); // Contoh: Membuat user dashboard terpisah
        }
    }

    /**
     * Display the admin (MC) dashboard.
     */
    public function adminDashboard()
    {
        $mc = Auth::user()->mc; // Ambil data MC yang terkait dengan user login
        $pendingBookings = $mc->bookings()->where('booking_status', 'pending_confirmation')->count();
        $confirmedBookings = $mc->bookings()->where('booking_status', 'confirmed')->count();
        // Anda bisa tambahkan data ringkasan lain di sini

        return view('admin.dashboard', compact('pendingBookings', 'confirmedBookings', 'mc'));
    }

    /**
     * Display the editor dashboard.
     */
    public function editorDashboard()
    {
        // Ambil data ringkasan untuk editor
        $totalUsers = \App\Models\User::count();
        $totalBookings = \App\Models\Booking::count();
        $totalRevenue = \App\Models\Booking::where('payment_status', 'fully_paid')->sum('grand_total');

        return view('editor.dashboard', compact('totalUsers', 'totalBookings', 'totalRevenue'));
    }
}