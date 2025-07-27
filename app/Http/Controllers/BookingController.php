<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Booking;
use App\Models\Mc;
use App\Models\McSchedule; // Jika diperlukan untuk cek ketersediaan
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function store(Request $request, string $mcId)
    {
        // ... (Validasi input lainnya tetap sama) ...

        // Validasi dan Ambil data dari Card List Item
        $request->validate([
            'selected_service_types' => 'required|string', // String 'lamaran,akad_nikah'
            'calculated_service_price' => 'required|numeric|min:0', // Harga yang dihitung dari frontend
        ]);

        $mc = Mc::findOrFail($mcId);
        /** @var User $user */
        $user = Auth::user();

        // Ambil data layanan dari request
        $selectedServiceTypes = explode(',', $request->input('selected_service_types')); // Convert string to array
        $priceFromFrontend = (float)$request->input('calculated_service_price');

        // VALIDASI HARGA DI BACKEND (PENTING UNTUK KEAMANAN!)
        // Kamu harus menghitung ulang harga di backend untuk mencegah manipulasi harga dari frontend.
        // Ini adalah contoh sederhana, kamu perlu logika yang lebih kompleks jika harga sangat dinamis.

        // Contoh dummy service rates (idealnya ini dari DB, mungkin dari mc->packages)
        $backendServiceRates = [
            'lamaran' => 650000,
            'akad_nikah' => 800000,
            'resepsi' => 800000,
            'paket_akad_resepsi' => 1000000,
            'paket_full_wedding' => 1500000,
            'other_events' => 700000,
        ];

        $calculatedBackendPrice = 0;
        $hasLamaran = in_array('lamaran', $selectedServiceTypes);
        $hasAkad = in_array('akad_nikah', $selectedServiceTypes);
        $hasResepsi = in_array('resepsi', $selectedServiceTypes);
        $hasOther = in_array('other_events', $selectedServiceTypes);
        $hasPaketAkadResepsi = in_array('paket_akad_resepsi', $selectedServiceTypes);
        $hasPaketFullWedding = in_array('paket_full_wedding', $selectedServiceTypes);


        if ($hasPaketFullWedding && isset($backendServiceRates['paket_full_wedding'])) {
            $calculatedBackendPrice = $backendServiceRates['paket_full_wedding'];
        } else if ($hasPaketAkadResepsi && isset($backendServiceRates['paket_akad_resepsi'])) {
            $calculatedBackendPrice = $backendServiceRates['paket_akad_resepsi'];
        } else {
            if ($hasLamaran && isset($backendServiceRates['lamaran'])) {
                $calculatedBackendPrice += $backendServiceRates['lamaran'];
            }
            if ($hasAkad && isset($backendServiceRates['akad_nikah'])) {
                $calculatedBackendPrice += $backendServiceRates['akad_nikah'];
            }
            if ($hasResepsi && isset($backendServiceRates['resepsi'])) {
                $calculatedBackendPrice += $backendServiceRates['resepsi'];
            }
            if ($hasOther && isset($backendServiceRates['other_events'])) {
                $calculatedBackendPrice += $backendServiceRates['other_events'];
            }
        }

        // Cek apakah harga dari frontend cocok dengan perhitungan backend (toleransi kecil untuk float)
        if (abs($priceFromFrontend - $calculatedBackendPrice) > 0.01) { // Toleransi 0.01
            throw ValidationException::withMessages([
                'calculated_service_price' => 'Terjadi ketidaksesuaian harga layanan. Silakan coba lagi. (Harga frontend: ' . $priceFromFrontend . ', Harga backend: ' . $calculatedBackendPrice . ')',
            ]);
        }

        // Gunakan calculatedBackendPrice untuk perhitungan total_amount
        $totalAmount = $calculatedBackendPrice; // Karena ini adalah harga layanan, bukan per jam lagi.
                                            // Jika MC punya rate per jam DAN layanan, perlu disesuaikan.
                                            // Untuk kasusmu, saya asumsikan ini adalah harga final layanan MC.

        $serviceFee = 25000.00;
        $grandTotal = $totalAmount + $serviceFee;
        $dpRequiredAmount = $grandTotal * 0.5;

        // ... (Kode perhitungan durasi tetap sama, tapi mungkin tidak relevan jika harga ditentukan per layanan/paket) ...
        // Jika kamu ingin harga layanan dan durasi bekerja bersama, maka:
        // $totalAmount = $calculatedBackendPrice * $durationHours; // Ini jika harga layanan juga per jam

        // Catatan: Jika layanan (misal Lamaran, Akad) sudah punya harga tetap,
        // maka rates_per_hour dan min_duration_hours di model MC mungkin tidak relevan untuk jenis layanan ini.
        // Kamu perlu memutuskan apakah MC dihargai per jam, per paket, atau kombinasi.
        // Berdasarkan deskripsimu "Lamaran, akad nikah dan resepsi" punya harga tetap,
        // sedangkan "seminar, gathering" punya rate 700.000, yang bisa diasumsikan per event, bukan per jam.
        // Jadi, saya akan asumsikan `total_amount` di `Booking` adalah harga layanan MC yang sudah fix.

        // ... (Buat Record Booking Baru di Database - bagian ini sama) ...
        $booking = Booking::create([
            'user_id'          => $user->id,
            'mc_id'            => $mc->id,
            'event_date'       => $request->input('event_date'),
            'event_start_time' => $request->input('event_start_time'),
            'event_end_time'   => $request->input('event_end_time'),
            'event_type'       => $request->input('event_type'), // Bisa diisi dengan gabungan selectedServiceTypes
            'location'         => $request->input('location'),
            'notes'            => $request->input('notes'),
            'total_amount'     => $totalAmount, // Ini adalah harga layanan yang dipilih
            'service_fee'      => $serviceFee,
            'grand_total'      => $grandTotal,
            'dp_required_amount' => $dpRequiredAmount,
            'paid_amount'      => 0,
            'remaining_amount' => $grandTotal,
            'payment_status'   => ($request->input('payment_option') === 'dp') ? 'pending_dp' : 'pending_full_payment',
            'booking_status'   => 'pending_confirmation',
            'selected_services' => implode(',', $selectedServiceTypes), // Simpan jenis layanan yang dipilih
        ]);

        return redirect()->route('booking.success', $booking->id)->with('success', 'Pemesanan berhasil dibuat, silakan lanjutkan pembayaran.');
    }
}