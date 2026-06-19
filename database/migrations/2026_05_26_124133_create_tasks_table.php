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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');      // Nama pelanggan (form admin)
            $table->string('device_name');        // Nama barang elektronik
            $table->text('damage_description');   // Deskripsi kerusakan barang
            
            // Relasi ke tabel users (Menghubungkan ke ID Teknisi Lapangan)
            $table->foreignId('technician_id')->constrained('users')->onDelete('cascade');
            
            // Kolom Koordinat & Jarak Otomatis (Syarat mutlak fitur Python Cloud)
            $table->string('latitude');
            $table->string('longitude');
            $table->double('distance_km')->nullable(); // Menampung hasil hitung km dari Python

            // STATUS WORKFLOW FLOW (Sesuai Aturan BRD Bab 9.1 & Coretan Lu)
            $table->enum('status', ['assigned', 'accepted', 'rejected', 'on-going', 'completed'])->default('assigned');
            
            $table->integer('cost')->default(0);       // Biaya servis (diinput teknisi pas kelar)
            $table->string('image_proof')->nullable(); // Link path foto bukti pekerjaan dari kamera HP
            $table->timestamps();                      // Mencatat created_at & updated_at otomatis
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};