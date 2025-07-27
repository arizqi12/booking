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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('bookings')->onDelete('cascade'); // 1 review per booking
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('mc_id')->constrained('mcs')->onDelete('cascade');
            $table->tinyInteger('rating')->unsigned(); // Rating 1-5
            $table->text('comment')->nullable();
            $table->timestamps();

            // Tambahkan constraint untuk rating
            $table->fulltext('comment'); // Untuk pencarian full-text pada komentar
        });

        // Contoh menambahkan CHECK constraint jika diperlukan (bisa juga divalidasi di aplikasi)
        // Schema::table('reviews', function (Blueprint $table) {
        //     $table->check('rating >= 1 AND rating <= 5');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};