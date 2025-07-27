<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Mc;
use App\Models\McSchedule;
use App\Models\McService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Tambahkan ini
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Store a newly created booking in storage.
     * Handles the initial booking request from the user.
     */
    public function store(Request $request, string $mcId)
    {
        Log::info('Booking process started.', ['mc_id' => $mcId, 'user_id' => Auth::id(), 'request_data' => $request->all()]);

        try {
            $request->validate([
                'fullName'                 => 'required|string|max:255',
                'email'                    => 'required|email|max:255',
                'phone'                    => 'required|string|max:20',
                'event_date'               => 'required|date_format:Y-m-d|after_or_equal:today',
                'event_start_time'         => 'required|date_format:H:i',
                'event_end_time'           => 'required|date_format:H:i', // Ini sudah benar tanpa 'after'
                'event_type'               => 'required|string|max:100',
                'location'                 => 'required|string|max:255',
                'notes'                    => 'nullable|string',
                'payment_option'           => 'required|in:full,dp',
                'agreeTerms'               => 'accepted',

                'selected_service_types'   => 'required|string',
                'calculated_service_price' => 'required|numeric|min:0',
            ]);
            Log::info('Booking request validation successful (pre-duration check).');
        } catch (ValidationException $e) {
            Log::warning('Booking request validation failed (pre-duration check).', ['errors' => $e->errors(), 'request_data' => $request->all()]);
            throw $e;
        }

        $mc = Mc::findOrFail($mcId);
        /** @var User $user */
        $user = Auth::user();

        // ... (Logika harga backend) ...
        $selectedServiceSlugs = explode(',', $request->input('selected_service_types'));
        $priceFromFrontend = (float)$request->input('calculated_service_price');

        $backendServices = McService::active()->whereIn('slug', $selectedServiceSlugs)->get()->keyBy('slug');

        $calculatedBackendPrice = 0;
        $selectedServiceNames = [];

        if (in_array('paket_full_wedding', $selectedServiceSlugs) && isset($backendServices['paket_full_wedding'])) {
            $calculatedBackendPrice = $backendServices['paket_full_wedding']->price;
            $selectedServiceNames[] = $backendServices['paket_full_wedding']->name;
        } elseif (in_array('paket_akad_resepsi', $selectedServiceSlugs) && isset($backendServices['paket_akad_resepsi'])) {
            $calculatedBackendPrice = $backendServices['paket_akad_resepsi']->price;
            $selectedServiceNames[] = $backendServices['paket_akad_resepsi']->name;
        } else {
            foreach ($selectedServiceSlugs as $slug) {
                if (isset($backendServices[$slug]) && $backendServices[$slug]->type === 'individual') {
                    $calculatedBackendPrice += $backendServices[$slug]->price;
                    $selectedServiceNames[] = $backendServices[$slug]->name;
                }
            }
        }
        Log::info('Backend calculated price for booking.', ['calculated_price' => $calculatedBackendPrice, 'selected_slugs' => $selectedServiceSlugs]);

        if (abs($priceFromFrontend - $calculatedBackendPrice) > 0.01) {
            Log::error('Frontend price mismatch detected!', [
                'frontend_price' => $priceFromFrontend,
                'backend_price' => $calculatedBackendPrice,
                'booking_data' => $request->all()
            ]);
            throw ValidationException::withMessages([
                'calculated_service_price' => 'Terjadi ketidaksesuaian harga layanan. Silakan coba lagi. (Harga F: Rp' . number_format($priceFromFrontend) . ', Harga B: Rp' . number_format($calculatedBackendPrice) . ')',
            ]);
        }
        Log::info('Frontend and backend prices matched.');

        // --- PERBAIKAN KRITIS DI SINI: Perhitungan Durasi Acara ---
        $eventDate      = $request->input('event_date');
        $eventStartTime = $request->input('event_start_time');
        $eventEndTime   = $request->input('event_end_time');

        // PASTIKAN TIMEZONE SAMA UNTUK KEDUA OBJEK CARBON
        $appTimezone = config('app.timezone');

        // Buat objek Carbon lengkap dengan tanggal dan waktu
        $startDateTime = Carbon::parse($eventDate . ' ' . $eventStartTime, $appTimezone);
        $endDateTime   = Carbon::parse($eventDate . ' ' . $eventEndTime, $appTimezone);

        // Debugging: Log objek Carbon yang terbentuk
        Log::debug('Carbon objects for duration calculation (with timezone).', [
            'start_datetime_obj' => $startDateTime->toDateTimeString(),
            'end_datetime_obj' => $endDateTime->toDateTimeString(),
            'start_tz' => $startDateTime->timezone->getName(),
            'end_tz' => $endDateTime->timezone->getName(),
            'start_is_after_end_initially' => $startDateTime->gt($endDateTime)
        ]);


        // Logika untuk menangani waktu yang melewati tengah malam:
        // Jika waktu selesai secara absolut lebih kecil dari waktu mulai (misal 22:00 -> 02:00),
        // maka asumsikan waktu selesai ada di hari berikutnya.
        if ($endDateTime->lt($startDateTime)) {
            $endDateTime->addDay();
            Log::info('Event spans across midnight for booking. Adjusted end date to next day.', [
                'original_start' => $startDateTime->toDateTimeString(),
                'original_end' => $endDateTime->subDay()->toDateTimeString(), // Log end date sebelum ditambah hari
                'adjusted_end' => $endDateTime->toDateTimeString()
            ]);
        }
        // Pastikan $endDateTime selalu >= $startDateTime setelah penyesuaian
        // Hitung durasi dalam menit, lalu konversi ke jam (float)
        $durationMinutes = abs($endDateTime->diffInMinutes($startDateTime)); // <-- THE CORRECTED LINE

        $durationHours = $durationMinutes / 60.0;

        // Validasi kustom untuk durasi non-positif setelah perhitungan
        if ($durationHours <= 0) {
            Log::warning('Booking duration invalid (zero or negative) after Carbon calculation.', [
                'requested_start_time' => $eventStartTime,
                'requested_end_time' => $eventEndTime,
                'calculated_duration_minutes' => $durationMinutes,
                'calculated_duration_hours' => $durationHours,
                'start_carbon_debug' => $startDateTime->toDateTimeString(),
                'end_carbon_debug' => $endDateTime->toDateTimeString()
            ]);
            throw ValidationException::withMessages([
                'event_end_time' => 'Waktu selesai harus setelah waktu mulai. Durasi acara tidak valid.'
            ]);
        }

        // Validasi Durasi Minimal MC
        if ($durationHours < (float)$mc->min_duration_hours) {
            Log::warning('Booking duration too short.', ['requested_duration' => $durationHours, 'min_duration' => (float)$mc->min_duration_hours]);
            throw ValidationException::withMessages([
                'event_end_time' => 'Durasi pemesanan minimal adalah ' . (float)$mc->min_duration_hours . ' jam. Durasi yang Anda pilih adalah ' . round($durationHours, 2) . ' jam.',
            ]);
        }
        Log::info('Booking duration validation successful.', ['duration_hours' => $durationHours]);

        // ... (Perhitungan Biaya Final dan pembuatan booking) ...
        $totalAmount = $calculatedBackendPrice;
        $serviceFee = 25000.00;
        $grandTotal = $totalAmount + $serviceFee;
        $dpRequiredAmount = $grandTotal * 0.5;
        Log::info('Final booking costs calculated.', ['grand_total' => $grandTotal, 'dp_required' => $dpRequiredAmount]);

        try {
            $booking = Booking::create([
                'user_id'          => $user->id,
                'mc_id'            => $mc->id,
                'event_date'       => $request->input('event_date'),
                'event_start_time' => $request->input('event_start_time'),
                'event_end_time'   => $request->input('event_end_time'),
                'event_type'       => implode(', ', $selectedServiceNames),
                'location'         => $request->input('location'),
                'notes'            => $request->input('notes'),
                'total_amount'     => $totalAmount,
                'service_fee'      => $serviceFee,
                'grand_total'      => $grandTotal,
                'dp_required_amount' => $dpRequiredAmount,
                'paid_amount'      => 0,
                'remaining_amount' => $grandTotal,
                'payment_status'   => ($request->input('payment_option') === 'dp') ? 'pending_dp' : 'pending_full_payment',
                'booking_status'   => 'pending_confirmation',
                'selected_services' => implode(',', $selectedServiceSlugs),
            ]);
            Log::info('Booking created successfully.', ['booking_id' => $booking->id, 'user_id' => $user->id, 'mc_id' => $mc->id]);
        } catch (\Exception $e) {
            Log::error('Failed to create booking in database.', ['error' => $e->getMessage(), 'request_data' => $request->all()]);
            return response()->json(['message' => 'Terjadi kesalahan saat menyimpan pemesanan.', 'error' => $e->getMessage()], 500);
        }

        return response()->json([
            'message' => 'Pemesanan berhasil dibuat!',
            'booking_id' => $booking->id,
            'redirect' => route('user.bookings.success', $booking->id)
        ], 200);
    }

    /**
     * Confirm a pending booking by the MC.
     */
    public function confirm(Request $request, string $id)
    {
        Log::info('Attempting to confirm booking.', ['booking_id' => $id, 'admin_user_id' => Auth::id()]);
        // ... (kode sebelumnya) ...
        $booking = $mc->bookings()->findOrFail($id);

        if (!in_array($booking->payment_status, ['dp_paid', 'fully_paid'])) {
            Log::warning('Booking confirmation failed: Payment not sufficient.', ['booking_id' => $id, 'current_payment_status' => $booking->payment_status]);
            return back()->with('error', 'Pembayaran booking ini belum diterima atau belum lunas. Tidak dapat mengkonfirmasi.');
        }

        if ($booking->booking_status !== 'pending_confirmation') {
            Log::warning('Booking confirmation failed: Not in pending status.', ['booking_id' => $id, 'current_booking_status' => $booking->booking_status]);
            return back()->with('error', 'Booking sudah tidak dalam status menunggu konfirmasi.');
        }

        $booking->booking_status = 'confirmed';
        $booking->save();
        Log::info('Booking confirmed successfully.', ['booking_id' => $booking->id]);

        return back()->with('success', 'Booking berhasil dikonfirmasi.');
    }

    public function success(string $id) // Ensure this method is present and public
    {
        Log::info('Accessing booking success page.', ['booking_id' => $id, 'user_id' => Auth::id()]);

        // Find the booking. Ensure it belongs to the authenticated user.
        /** @var User $user */
        $user = Auth::user();
        $booking = $user->bookings()->with('mc.user')->findOrFail($id); // Load MC and user data

        return view('user.bookings.success', compact('booking'));
    }

    /**
     * Reject a pending booking by the MC.
     */
    public function reject(Request $request, string $id)
    {
        Log::info('Attempting to reject booking.', ['booking_id' => $id, 'admin_user_id' => Auth::id(), 'reason' => $request->input('reason')]);
        // ... (kode sebelumnya) ...
        try {
            $request->validate(['reason' => 'required|string|max:500']);
        } catch (ValidationException $e) {
            Log::warning('Booking rejection reason validation failed.', ['booking_id' => $id, 'errors' => $e->errors()]);
            throw $e;
        }

        $booking = $mc->bookings()->findOrFail($id);

        if ($booking->booking_status !== 'pending_confirmation') {
            Log::warning('Booking rejection failed: Not in pending status.', ['booking_id' => $id, 'current_booking_status' => $booking->booking_status]);
            return back()->with('error', 'Booking sudah tidak dalam status menunggu konfirmasi.');
        }

        $booking->booking_status = 'rejected';
        $booking->cancellation_reason = $request->input('reason');
        $booking->save();
        Log::info('Booking rejected successfully.', ['booking_id' => $booking->id, 'reason' => $request->input('reason')]);

        return back()->with('success', 'Booking berhasil ditolak.');
    }
}