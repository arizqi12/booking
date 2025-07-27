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
        Schema::create('mc_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mc_id')->constrained('mcs')->onDelete('cascade');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true); // false jika MC memblokir secara manual
            $table->timestamps();

            // Tambahkan index unik untuk mencegah duplikasi slot waktu di tanggal yang sama oleh MC yang sama
            $table->unique(['mc_id', 'date', 'start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mc_schedules');
    }
};