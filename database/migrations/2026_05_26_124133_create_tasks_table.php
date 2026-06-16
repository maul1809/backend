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
            $table->string('customer_name');
            $table->string('device_name');
            $table->text('damage_description');
            
            
            $table->foreignId('technician_id')->constrained('users')->onDelete('cascade');
            
            $table->enum('status', ['pending', 'processing', 'done'])->default('pending');
            $table->integer('cost')->default(0);
            $table->string('image_proof')->nullable();
            $table->timestamps();
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