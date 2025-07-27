<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\Log; // Untuk logging

class PaymentController extends Controller
{
    public function __construct()
    {
        // Konfigurasi Midtrans di constructor
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Generate Snap Token for a booking.
     */
    public function getSnapToken(Request $request, Booking $booking)
    {
        // Pastikan hanya user yang memiliki booking ini yang bisa generate token
        if (auth()->id() !== $booking->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Tentukan jumlah pembayaran berdasarkan jenis (DP atau Pelunasan)
        $paymentType = $request->query('type', 'initial_payment'); // Default: pembayaran awal (DP/Full)

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
                return response()->json(['message' => 'Booking not in initial payment state.'], 400);
            }
        } elseif ($paymentType === 'remaining_payment') {
            if ($booking->payment_status === 'dp_paid' && $booking->remaining_amount > 0) {
                $amountToPay = $booking->remaining_amount;
                $transactionDetailsName = "Remaining Payment Booking MC #" . $booking->id;
            } else {
                return response()->json(['message' => 'Booking not in remaining payment state or fully paid.'], 400);
            }
        } else {
            return response()->json(['message' => 'Invalid payment type.'], 400);
        }

        if ($amountToPay <= 0) {
            return response()->json(['message' => 'Amount to pay is zero or negative.'], 400);
        }

        // Parameter untuk Midtrans Snap
        $params = array(
            'transaction_details' => array(
                'order_id' => $booking->id . '-' . uniqid(), // Order ID unik untuk Midtrans
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
                // 'phone' => $booking->user->phone, // Jika ada kolom phone di User
            ),
            'callbacks' => [ // URLs to listen for Midtrans notifications
                'finish' => route('my.bookings.show', $booking->id), // Redirect back to booking detail
                'error' => route('my.bookings.show', $booking->id),
                'pending' => route('my.bookings.show', $booking->id),
            ]
        );

        try {
            $snapToken = Snap::getSnapToken($params);

            // Simpan snap token terakhir ke booking
            $booking->midtrans_snap_token = $snapToken;
            $booking->save();

            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            Log::error('Error generating Midtrans Snap Token: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to generate payment token: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle Midtrans Payment Notification (Webhook).
     */
    public function handleNotification(Request $request)
    {
        $notif = new \Midtrans\Notification();

        $transaction = $notif->transaction_status;
        $type = $notif->payment_type;
        $orderId = explode('-', $notif->order_id)[0]; // Ambil booking_id dari order_id Midtrans
        $fraud = $notif->fraud_status;
        $amount = $notif->gross_amount; // Jumlah yang dibayar Midtrans

        $booking = Booking::find($orderId);

        if (!$booking) {
            Log::warning("Midtrans Notification: Booking ID not found.", ['order_id' => $orderId, 'notif' => $notif->json()]);
            return response()->json(['message' => 'Booking not found'], 404);
        }

        // Cek signature key untuk validasi keamanan
        $hashed = hash('sha512', $notif->order_id . $notif->status_code . $notif->gross_amount . config('midtrans.server_key'));
        if ($hashed != $notif->signature_key) {
            Log::warning("Midtrans Notification: Invalid signature key.", ['order_id' => $orderId, 'notif' => $notif->json()]);
            return response()->json(['message' => 'Invalid signature key'], 403);
        }

        if ($transaction == 'capture') {
            if ($fraud == 'challenge') {
                // TODO: Handle challenge status (pending)
                $paymentStatus = 'pending';
            } else if ($fraud == 'accept') {
                // Payment successful (credit card)
                $paymentStatus = 'success';
            }
        } else if ($transaction == 'settlement') {
            // Payment successful (other methods like VA, e-money)
            $paymentStatus = 'success';
        } else if ($transaction == 'pending') {
            $paymentStatus = 'pending';
        } else if ($transaction == 'deny') {
            $paymentStatus = 'failed';
        } else if ($transaction == 'expire') {
            $paymentStatus = 'failed';
        } else if ($transaction == 'cancel') {
            $paymentStatus = 'failed';
        } else if ($transaction == 'refund' || $transaction == 'partial_refund' || $transaction == 'chargeback') {
            $paymentStatus = 'refunded'; // Atau 'partial_refunded'
        } else {
            $paymentStatus = 'unknown';
        }

        // Update status pembayaran di tabel payments dan booking
        if ($paymentStatus == 'success') {
            // Catat pembayaran baru
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'amount' => $amount,
                'payment_type' => ($booking->payment_status === 'pending_dp' || $booking->payment_status === 'dp_paid') ? 'down_payment' : 'full_payment', // Bisa diadjust
                'midtrans_transaction_id' => $notif->transaction_id,
                'midtrans_status' => $transaction,
                'payment_method' => $type,
                'transaction_time' => $notif->transaction_time,
                'raw_response' => $notif->json(),
            ]);

            // Update booking status
            $booking->paid_amount += $amount; // Tambahkan jumlah yang baru dibayar
            $booking->remaining_amount = $booking->grand_total - $booking->paid_amount;

            if ($booking->remaining_amount <= 0) {
                $booking->payment_status = 'fully_paid';
            } else {
                $booking->payment_status = 'dp_paid'; // Jika belum lunas, tapi DP sudah masuk
            }
            $booking->midtrans_last_trx_id = $notif->transaction_id;
            $booking->save();

            Log::info("Midtrans Notification: Booking ID #{$booking->id} updated to status '{$booking->payment_status}'.", ['notif' => $notif->json()]);

            // TODO: Kirim notifikasi ke user dan MC bahwa pembayaran berhasil
        } else if ($paymentStatus == 'failed' || $paymentStatus == 'refunded') {
            // Jika pembayaran gagal atau direfund, update status booking ke failed/canceled
            // Atau hanya mencatat pembayaran gagal tanpa mengubah status booking secara drastis
            Log::info("Midtrans Notification: Booking ID #{$booking->id} payment failed/refunded. Status: {$transaction}", ['notif' => $notif->json()]);

            // Anda bisa tambahkan logika untuk mengubah booking_status ke 'canceled' jika full payment gagal
            // atau jika DP gagal dan booking masih pending_confirmation
            if ($booking->payment_status === 'pending_dp' || $booking->payment_status === 'pending_full_payment') {
                $booking->payment_status = 'failed';
                $booking->booking_status = 'canceled'; // Atau status lain
                $booking->cancellation_reason = 'Payment failed/expired via Midtrans.';
                $booking->save();
                Log::info("Booking #{$booking->id} canceled due to payment failure.");
            }
        } else {
             Log::info("Midtrans Notification: Booking ID #{$booking->id} status is {$transaction}, no action taken.", ['notif' => $notif->json()]);
        }

        return response()->json(['message' => 'Notification processed successfully']);
    }
}