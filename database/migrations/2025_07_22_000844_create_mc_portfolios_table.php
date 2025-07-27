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
        Schema::create('mc_portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mc_id')->constrained('mcs')->onDelete('cascade'); // Foreign Key to mcs table
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('media_path'); // URL atau path ke file media
            $table->string('media_type', 50); // 'image' atau 'video'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mc_portfolios');
    }
};