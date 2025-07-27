<?php

namespace App\Http\Controllers;

use App\Models\Mc;
use App\Models\Booking; // Tambahkan ini
use App\Models\McService;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

    /**
     * Get MC schedules for FullCalendar (API endpoint).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mc  $mc  // Pastikan ini menggunakan type hint Model Mc
     */
    public function getSchedules(Request $request, Mc $mc) // Ubah string $mcId menjadi Mc $mc
    {
        // Ambil jadwal yang tersedia untuk MC ini
        $schedules = $mc->schedules()
                        ->where('date', '>=', Carbon::now()->toDateString()) // Hanya tanggal di masa depan
                        ->get();

        $events = [];
        $bookedDates = [];

        // Ambil tanggal-tanggal yang sudah dibooking dengan status 'confirmed' atau 'dp_paid' (belum cancel/rejected)
        $bookings = $mc->bookings()
                        ->whereIn('booking_status', ['pending_confirmation', 'confirmed'])
                        ->whereIn('payment_status', ['pending_dp', 'dp_paid', 'fully_paid'])
                        ->where('event_date', '>=', Carbon::now()->toDateString())
                        ->get();

        foreach ($bookings as $booking) {
            $bookedDates[] = $booking->event_date;
        }

        // Tambahkan tanggal yang MC sendiri blokir sebagai 'tidak tersedia'
        foreach ($schedules as $schedule) {
            // Pastikan tidak ada duplikasi tanggal terblokir dari booking
            if (!$schedule->is_available && !in_array($schedule->date, $bookedDates)) {
                $bookedDates[] = $schedule->date;
            }
        }

        // Tambahkan semua tanggal yang terisi sebagai background events di FullCalendar
        foreach (array_unique($bookedDates) as $date) {
            $events[] = [
                'start' => $date,
                'display' => 'background',
                'color' => '#f0f0f0', // Warna untuk tanggal yang tidak bisa dipilih
                'classNames' => ['fc-day-disabled'] // Tambahkan class untuk styling CSS
            ];
        }

        return response()->json([
            'schedules' => $schedules,
            'events' => $events
        ]);
    }
    public function getServices() // Tidak ada parameter di sini
    {
        $services = McService::active()->orderBy('type', 'desc')->orderBy('name')->get();
        return response()->json(['services' => $services]);
    }

}