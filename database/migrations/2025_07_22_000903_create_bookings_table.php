<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Pemesan
            $table->foreignId('mc_id')->constrained('mcs')->onDelete('cascade'); // MC yang dipesan
            $table->foreignId('schedule_id')->nullable()->constrained('mc_schedules')->onDelete('set null'); // Bisa null jika booking tidak terkait slot spesifik
            $table->date('event_date');
            $table->time('event_start_time');
            $table->time('event_end_time');
            $table->string('event_type', 100);
            $table->string('location');
            $table->text('notes')->nullable();

            // Informasi harga dan pembayaran
            $table->decimal('total_amount', 10, 2);
            $table->decimal('service_fee', 10, 2);
            $table->decimal('grand_total', 10, 2);
            $table->decimal('dp_required_amount', 10, 2); // DP 50%
            $table->decimal('paid_amount', 10, 2)->default(0.00); // Jumlah yang sudah dibayar
            $table->decimal('remaining_amount', 10, 2); // Sisa pembayaran

            // Status
            $table->string('payment_status', 50)->default('pending_dp'); // pending_dp, dp_paid, fully_paid, refunded, failed
            $table->string('booking_status', 50)->default('pending_confirmation'); // pending_confirmation, confirmed, rejected, completed, canceled

            $table->string('midtrans_last_trx_id')->nullable(); // ID transaksi Midtrans terakhir
            $table->string('midtrans_snap_token')->nullable(); // Snap token terakhir
            $table->text('cancellation_reason')->nullable(); // Alasan jika dibatalkan

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};