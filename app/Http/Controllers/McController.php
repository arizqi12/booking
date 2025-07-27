<?php

namespace App\Http\Controllers;

use App\Models\Mc;
use Illuminate\Http\Request;

class McController extends Controller
{
    /**
     * Display the specified MC's profile.
     */
    public function show(string $id)
    {
        $mc = Mc::with(['portfolios', 'schedules', 'reviews.user'])->findOrFail($id);
        // Anda bisa tambahkan logika untuk mengambil ketersediaan khusus Sabtu/Minggu di sini
        // Atau nanti akan ditangani oleh JS di frontend

        return view('mc.show', compact('mc'));
    }
}