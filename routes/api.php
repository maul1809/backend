<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\TaskController;

// ==========================================
// ROUTE PUBLIK (Bisa diakses tanpa login/token)
// ==========================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ==========================================
// ROUTE PRIVAT (Wajib menyertakan Bearer Token di Postman)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    
    // Cek profil user yang sedang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // ------------------------------------------
    // Urusan Fitur Tugas/Order Servis (Aplikasi Customer & Teknisi)
    // ------------------------------------------
    
    // 1. Lihat semua tugas (Dipakai Admin/Teknisi/Customer)
    Route::get('/tasks', [TaskController::class, 'index']);            
    
    // 2. Buat tugas baru + Hitung jarak Python (Ditembak dari APK Customer)
    Route::post('/tasks', [TaskController::class, 'store']);            
    
    // 3. Update status & biaya tugas (Ditembak dari APK Teknisi pas kerja/selesai)
    Route::put('/tasks/{id}/update-status', [TaskController::class, 'updateStatus']); 
});