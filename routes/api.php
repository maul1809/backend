<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;     
use App\Http\Controllers\Api\TrackingController; 
use Illuminate\Support\Facades\Artisan;

// ==========================================
// ROUTE PUBLIK (Bisa diakses tanpa login/token)
// ==========================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// ==========================================
// ROUTE PRIVAT (Wajib menyertakan Bearer Token)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    
    // Cek profil user/teknisi yang sedang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // ------------------------------------------
    // MODUL 1: TASK MANAGEMENT 
    // ------------------------------------------
    
    // 1. Lihat semua tugas AKTIF yang belum selesai (Web Admin & Mobile Teknisi)
    Route::get('/tasks', [TaskController::class, 'index']);            
    
    // 2. Buat tugas baru + Hitung jarak otomatis Python Cloud (Ditembak Web Admin)
    Route::post('/tasks', [TaskController::class, 'store']); 

    // 3. Update status alur kerja & upload foto bukti (Ditembak dari APK Mobile Teknisi)
    Route::put('/tasks/{id}/update-status', [TaskController::class, 'updateStatus']); 


    // ------------------------------------------
    // MODUL 2: HISTORI PEKERJAAN 
    // ------------------------------------------
    
    // 4. Ambil riwayat tugas yang sudah selesai/ditolak (completed / rejected)
    Route::get('/tasks/history', [TaskController::class, 'history']);


    // ------------------------------------------
    // MODUL 3: REALTIME TRACKING TEKNISI 
    // ------------------------------------------
    
    // 5. Kirim koordinat GPS HP Teknisi secara berkala dari Ionic ke Backend
    Route::post('/tracking', [TrackingController::class, 'store']);

    // 6. Ambil koordinat TERAKHIR teknisi untuk digambar ke OpenStreetMap Web Admin (BARU)
    Route::get('/tracking/latest/{task_id}', [TrackingController::class, 'getLatestLocation']);
});


// ==========================================
//  PEMBERSIH CACHE & REFRESH SISTEM
// ==========================================
Route::get('/artisan-fms', function() {
    // Bersihkan semua jenis cache 
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    
    return "Sistem dibersihkan!";
});