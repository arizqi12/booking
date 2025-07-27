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
        Schema::create('mcs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Foreign Key to users table
            $table->text('bio')->nullable();
            $table->decimal('rates_per_hour', 10, 2); // Harga dasar per jam
            $table->decimal('min_duration_hours', 4, 2)->default(1.0); // Durasi minimal pemesanan (e.g., 1.5 for 1.5 hours)
            $table->string('contact_phone', 20)->nullable();
            $table->string('profile_picture_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcs');
    }
};