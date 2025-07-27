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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('payment_type', 50); // 'down_payment', 'full_payment', 'remaining_payment'
            $table->string('midtrans_transaction_id')->unique(); // ID unik dari Midtrans
            $table->string('midtrans_status', 50); // Status dari Midtrans (e.g., settlement, pending, deny)
            $table->string('payment_method', 100)->nullable(); // e.g., 'gopay', 'bca_va'
            $table->timestamp('transaction_time')->nullable(); // Waktu transaksi di Midtrans
            $table->json('raw_response')->nullable(); // Simpan response webhook mentah untuk debugging
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};