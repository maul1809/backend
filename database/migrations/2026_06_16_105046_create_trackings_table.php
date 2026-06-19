<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trackings', function (Blueprint $table) {
            $table->id();
            // Menghubungkan lokasi ini dengan ID user/teknisi yang sedang jalan
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('latitude');
            $table->string('longitude');
            $table->timestamps(); // Ini otomatis mencatat waktu (created_at) kapan koordinat dikirim
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trackings');
    }
};