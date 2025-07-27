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
        Schema::create('mc_services', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Lamaran", "Paket Akad & Resepsi"
            $table->string('slug')->unique(); // e.g., "lamaran", "paket_akad_resepsi"
            $table->string('type', 50); // 'individual', 'package'
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->json('included_services')->nullable(); // Untuk paket, daftar slug layanan yang termasuk
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mc_services');
    }
};