<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
        Log::info('Midtrans Config loaded.', ['is_production' => Config::$isProduction]);
    }

    /**
     * Generate Snap Token for a booking.
     */
    public function getSnapToken(Request $request, Booking $booking)
    {
        Log::info('Request to generate Snap Token.', ['booking_id' => $booking->id, 'user_id' => Auth::id(), 'payment_type_request' => $request->query('type')]);

        if (Auth::id() !== $booking->user_id) {
            Log::warning('Unauthorized attempt to generate Snap Token.', ['booking_id' => $booking->id, 'attempted_user_id' => Auth::id()]);
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $paymentType = $request->query('type', 'initial_payment');
        $amountToPay = 0;
        $transactionDetailsName = "";

        if ($paymentType === 'initial_payment') {
            if ($booking->payment_status === 'pending_dp') {
                $amountToPay = $booking->dp_required_amount;
                $transactionDetailsName = "Down Payment Booking MC #" . $booking->id;
            } elseif ($booking->payment_status === 'pending_full_payment') {
                $amountToPay = $booking->grand_total;
                $transactionDetailsName = "Full Payment Booking MC #" . $booking->id;
            } else {
                Log::warning('Invalid booking status for initial payment.', ['booking_id' => $booking->id, 'status' => $booking->payment_status]);
                return response()->json(['message' => 'Booking not in initial payment state.'], 400);
            }
        } elseif ($paymentType === 'remaining_payment') {
            if ($booking->payment_status === 'dp_paid' && $booking->remaining_amount > 0) {
                $amountToPay = $booking->remaining_amount;
                $transactionDetailsName = "Remaining Payment Booking MC #" . $booking->id;
            } else {
                Log::warning('Invalid booking status for remaining payment.', ['booking_id' => $booking->id, 'status' => $booking->payment_status, 'remaining_amount' => $booking->remaining_amount]);
                return response()->json(['message' => 'Booking not in remaining payment state or fully paid.'], 400);
            }
        } else {
            Log::warning('Invalid payment type requested.', ['payment_type_request' => $paymentType]);
            return response()->json(['message' => 'Invalid payment type.'], 400);
        }

        if ($amountToPay <= 0) {
            Log::warning('Attempt to generate Snap Token with zero or negative amount.', ['booking_id' => $booking->id, 'amount' => $amountToPay]);
            return response()->json(['message' => 'Amount to pay is zero or negative.'], 400);
        }

        $params = array(
            'transaction_details' => array(
                // Untuk pengujian cepat, gunakan hanya booking->id.
                // Untuk production, tambahkan `-' . uniqid()` dan simpan string lengkap ini ke `midtrans_order_id` di DB.
                'order_id' => (string)$booking->id, 
                'gross_amount' => $amountToPay,
            ),
            'item_details' => [
                [
                    'id' => $booking->id,
                    'price' => $amountToPay,
                    'quantity' => 1,
                    'name' => $transactionDetailsName,
                ]
            ],
            'customer_details' => array(
                'first_name' => $booking->user->name,
                'email' => $booking->user->email,
            ),
            /*
            'callbacks' => [
                'finish' => route('my.bookings.show', $booking->id),
                'error' => route('my.bookings.show', $booking->id),
                'pending' => route('my.bookings.show', $booking->id),
            ]
            */
        );

        try {
            $snapToken = Snap::getSnapToken($params);
            $booking->midtrans_snap_token = $snapToken;
            // Jika Anda menambahkan midtrans_order_id, simpan di sini:
            // $booking->midtrans_order_id = $params['transaction_details']['order_id'];
            $booking->save();
            Log::info('Snap Token generated successfully.', ['booking_id' => $booking->id, 'snap_token_prefix' => substr($snapToken, 0, 10)]);
            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            Log::error('Error generating Midtrans Snap Token: ' . $e->getMessage(), ['exception' => $e, 'booking_id' => $booking->id]);
            return response()->json(['message' => 'Failed to generate payment token: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle Midtrans notification (webhook).
     * Ini tetap kita biarkan ada, tapi tidak akan dipanggil oleh Midtrans jika callbacks dihapus.
     */
    public function handleNotification(Request $request)
    {
        Log::info('Midtrans notification received.', ['request' => $request->all()]);

        // Gunakan \Midtrans\Notification() untuk parsing
        $notif = new \Midtrans\Notification();

        // Parameter dari notif webhook juga diakses sebagai properti objek secara default
        $transactionStatus = $notif->transaction_status;
        $fraudStatus = $notif->fraud_status;
        $orderId = explode('-', $notif->order_id)[0]; // Ambil booking_id dari order_id
        $grossAmount = $notif->gross_amount; // Jumlah yang dibayar

        $booking = Booking::find($orderId);

        if (!$booking) {
            Log::warning('Booking not found for notification.', ['order_id' => $orderId]);
            return response('Booking not found', 404);
        }

        // Cek signature key untuk validasi keamanan
        $hashed = hash('sha512', $notif->order_id . $notif->status_code . $notif->gross_amount . config('midtrans.server_key'));
        if ($hashed != $notif->signature_key) {
            Log::error("Midtrans Notification: Invalid signature key.", ['order_id' => $orderId, 'notif_raw' => json_encode($notif)]);
            return response('Invalid signature key', 403);
        }
        Log::info('Midtrans Webhook signature verified.', ['order_id' => $orderId]);

        // Panggil helper untuk update status
        $this->updateBookingStatusFromMidtransResponse($booking, (array) $notif); // Cast $notif menjadi array
        
        return response('OK', 200);
    }

    /**
     * Method untuk mengecek status transaksi Midtrans secara manual (GET)
     * Ini akan menjadi pengganti sementara webhook untuk testing.
     */
    public function checkTransactionStatus(Request $request, Booking $booking)
    {
        Log::info('Manual check of Midtrans transaction status requested.', ['booking_id' => $booking->id, 'user_id' => Auth::id()]);

        if (Auth::id() !== $booking->user_id) {
            Log::warning('Unauthorized attempt to check transaction status.', ['booking_id' => $booking->id, 'attempted_user_id' => Auth::id()]);
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Mengambil order_id yang digunakan di Midtrans
        // Jika Anda menyimpan order_id lengkap (misal: 1-abcdef123) di `midtrans_order_id` di DB, gunakan itu.
        // Jika tidak, dan Anda hanya mengirim booking->id, maka cukup `(string)$booking->id`.
        $midtransOrderId = (string) $booking->midtrans_order_id ?? (string) $booking->id; 

        if (empty($midtransOrderId)) {
             Log::warning('No Midtrans Order ID found for booking, cannot check status.', ['booking_id' => $booking->id]);
             return response()->json(['message' => 'No payment initiated for this booking or Midtrans Order ID is missing.'], 400);
        }

        try {
            // \Midtrans\Transaction::status() mengembalikan objek yang bisa diakses seperti array atau objek
            $statusResponse = \Midtrans\Transaction::status($midtransOrderId);
            
            // Konversi respons objek Midtrans menjadi array untuk konsistensi
            // Atau, bisa juga langsung gunakan $statusResponse->transaction_status, dll.
            $statusArray = (array) $statusResponse; 

            Log::info('Midtrans transaction status fetched.', ['booking_id' => $booking->id, 'midtrans_order_id' => $midtransOrderId, 'midtrans_status' => $statusArray['transaction_status'] ?? 'N/A']);

            // Update status booking berdasarkan respons Midtrans
            $this->updateBookingStatusFromMidtransResponse($booking, $statusArray); // Kirim sebagai array
            
            return response()->json(['status' => $statusArray['transaction_status'], 'booking_status' => $booking->payment_status]);

        } catch (\Exception $e) {
            Log::error('Error checking Midtrans transaction status: ' . $e->getMessage(), ['exception' => $e, 'booking_id' => $booking->id, 'midtrans_order_id' => $midtransOrderId]);
            return response()->json(['message' => 'Failed to check payment status: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper method to update booking status based on Midtrans response.
     * Dapat digunakan oleh webhook dan manual check.
     * @param \App\Models\Booking $booking
     * @param array|\stdClass $status Ini bisa objek (dari Notification) atau array (dari Transaction::status)
     */
    protected function updateBookingStatusFromMidtransResponse(Booking $booking, $status)
    {
        // Pastikan kita bisa mengakses properti status baik dari objek atau array
        $transactionStatus = is_array($status) ? ($status['transaction_status'] ?? null) : ($status->transaction_status ?? null);
        $fraudStatus       = is_array($status) ? ($status['fraud_status'] ?? null)       : ($status->fraud_status ?? null);
        $grossAmount       = is_array($status) ? ($status['gross_amount'] ?? null)       : ($status->gross_amount ?? null);
        $paymentType       = is_array($status) ? ($status['payment_type'] ?? null)       : ($status->payment_type ?? null);
        $transactionTime   = is_array($status) ? ($status['transaction_time'] ?? null)   : ($status->transaction_time ?? null);
        $transactionId     = is_array($status) ? ($status['transaction_id'] ?? null)     : ($status->transaction_id ?? null);
        $orderId           = is_array($status) ? ($status['order_id'] ?? null)           : ($status->order_id ?? null);


        Log::info('Updating booking status from Midtrans response.', [
            'booking_id' => $booking->id,
            'midtrans_transaction_status' => $transactionStatus,
            'midtrans_fraud_status' => $fraudStatus,
            'current_payment_status_db' => $booking->payment_status
        ]);

        if (!$transactionStatus) {
            Log::warning('Midtrans response missing transaction_status.', ['status_data' => $status]);
            return; // Tidak bisa update jika status tidak ada
        }


        // Cek apakah payment status di DB sudah fully_paid dan notifikasi datang lagi untuk settlement
        // Ini untuk mencegah update berulang pada booking yang sudah lunas
        if ($booking->payment_status === 'fully_paid' && $transactionStatus === 'settlement') { // settlement biasanya berarti sukses
             Log::info("Booking already fully paid, skipping update from successful notification.", ['booking_id' => $booking->id]);
             return;
        }


        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $booking->payment_status = 'pending'; // 'challenge' is pending
            } else if ($fraudStatus == 'accept') {
                $booking->payment_status = 'dp_paid'; // Consider capture accept as DP paid
            }
        } else if ($transactionStatus == 'settlement') {
            $booking->payment_status = 'fully_paid'; // Settlement means successful full payment
        } else if ($transactionStatus == 'pending') {
            $booking->payment_status = 'pending_dp'; // Back to pending dp or full payment
        } else if ($transactionStatus == 'deny') {
            $booking->payment_status = 'failed';
        } else if ($transactionStatus == 'expire') {
            $booking->payment_status = 'failed'; // Expired transaction means failed
        } else if ($transactionStatus == 'cancel') {
            $booking->payment_status = 'failed'; // Cancelled transaction means failed
        } else if (in_array($transactionStatus, ['refund', 'partial_refund', 'chargeback'])) {
            $booking->payment_status = 'refunded';
        } else {
            Log::warning('Unhandled Midtrans transaction status.', ['status' => $transactionStatus, 'booking_id' => $booking->id]);
            return; // Jangan update jika status tidak dikenal
        }
        
        // Update paid_amount dan remaining_amount hanya jika payment_status bukan failed/refunded/cancelled
        // dan belum sepenuhnya terbayar di DB
        if (!in_array($booking->payment_status, ['failed', 'expired', 'cancelled', 'refunded']) && $booking->payment_status !== 'fully_paid') {
            $currentPaidAmount = (float) $booking->paid_amount;
            $incomingAmount = (float) $grossAmount;

            // Hanya tambahkan jika jumlah yang masuk itu baru atau belum tercatat
            // Ini asumsi sederhana, logika yang lebih kompleks mungkin perlu cek payment record sebelumnya.
            if ($currentPaidAmount < $booking->grand_total && $incomingAmount > 0) {
                 $booking->paid_amount = $currentPaidAmount + $incomingAmount;
                 $booking->remaining_amount = $booking->grand_total - $booking->paid_amount;
                 if ($booking->remaining_amount <= 0.01) { // Toleransi float untuk 0
                     $booking->payment_status = 'fully_paid';
                     Log::info('Booking updated to fully_paid via incoming payment.', ['booking_id' => $booking->id, 'incoming_amount' => $incomingAmount]);
                 }
            }
        }


        $booking->save();
        Log::info('Booking status updated based on Midtrans response.', ['booking_id' => $booking->id, 'new_app_payment_status' => $booking->payment_status, 'midtrans_trx_status' => $transactionStatus]);

        // Tambahkan juga record Payment di tabel payments
        try {
            Payment::create([
                'booking_id' => $booking->id,
                'amount' => (float) $grossAmount,
                'payment_type' => $paymentType, // Dari Snap atau ditentukan di sini (initial/remaining)
                'midtrans_transaction_id' => $transactionId,
                'midtrans_status' => $transactionStatus,
                'payment_method' => $paymentType, // Bisa disesuaikan dari respons Midtrans (e.g., 'gopay')
                'transaction_time' => $transactionTime,
                'raw_response' => json_encode($status), // Simpan respons mentah
            ]);
            Log::info('Payment record created for booking.', ['booking_id' => $booking->id, 'amount' => $grossAmount, 'trx_id' => $transactionId]);
        } catch (\Exception $e) {
            Log::error('Failed to create Payment record for Midtrans transaction.', ['error' => $e->getMessage(), 'booking_id' => $booking->id, 'midtrans_status' => $transactionStatus]);
        }
    }
}