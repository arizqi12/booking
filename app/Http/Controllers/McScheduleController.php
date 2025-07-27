<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\McSchedule;
use App\Models\Mc; // Pastikan ini diimpor
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // Pastikan ini diimpor

class McScheduleController extends Controller
{
    /**
     * Display a listing of MC's schedules. (For MC Admin Dashboard)
     */
    public function index()
    {
        $mc = Auth::user()->mc; // Ambil data MC yang terkait dengan user login
        if (!$mc) {
            return redirect()->back()->with('error', 'Anda bukan MC.');
        }

        // Ambil jadwal MC untuk beberapa bulan ke depan atau yang sudah ada
        $schedules = $mc->schedules()->orderBy('date')->get();

        return view('admin.schedules.index', compact('mc', 'schedules'));
    }

    /**
     * Store a new MC schedule or update existing one.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_available' => 'required|boolean',
        ]);

        $mc = Auth::user()->mc;
        if (!$mc) {
            return redirect()->back()->with('error', 'Anda bukan MC.');
        }

        $selectedDate = Carbon::parse($request->date);

        // Validasi: hanya izinkan Sabtu (6) dan Minggu (0)
        if ($selectedDate->dayOfWeek !== Carbon::SATURDAY && $selectedDate->dayOfWeek !== Carbon::SUNDAY) {
            return redirect()->back()->withErrors(['date' => 'Jadwal hanya bisa diatur pada hari Sabtu atau Minggu.'])->withInput();
        }

        // Cek apakah slot jadwal sudah ada
        $schedule = McSchedule::where('mc_id', $mc->id)
                              ->where('date', $request->date)
                              ->where('start_time', $request->start_time)
                              ->where('end_time', $request->end_time)
                              ->first();

        if ($schedule) {
            // Update jadwal yang sudah ada
            $schedule->is_available = $request->is_available;
            $schedule->save();
            $message = 'Jadwal berhasil diperbarui.';
        } else {
            // Buat jadwal baru
            McSchedule::create([
                'mc_id' => $mc->id,
                'date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'is_available' => $request->is_available,
            ]);
            $message = 'Jadwal baru berhasil ditambahkan.';
        }

        return redirect()->route('admin.schedules.index')->with('success', $message);
    }

    /**
     * Delete an MC schedule.
     */
    public function destroy(string $id)
    {
        $mc = Auth::user()->mc;
        if (!$mc) {
            return redirect()->back()->with('error', 'Anda bukan MC.');
        }

        $schedule = McSchedule::where('mc_id', $mc->id)->findOrFail($id);

        // Cek apakah ada booking yang terkait dengan jadwal ini dan belum selesai/dibatalkan
        $activeBookings = $schedule->bookings()
                                 ->whereIn('booking_status', ['pending_confirmation', 'confirmed'])
                                 ->count();

        if ($activeBookings > 0) {
            return redirect()->back()->with('error', 'Tidak bisa menghapus jadwal karena ada pemesanan aktif yang terkait.');
        }

        $schedule->delete();

        return redirect()->route('admin.schedules.index')->with('success', 'Jadwal berhasil dihapus.');
    }
}