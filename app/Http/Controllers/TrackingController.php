<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tracking;
use Illuminate\Support\Facades\Validator;

class TrackingController extends Controller
{
    // Fungsi untuk menerima setoran koordinat dari Ionic HP Teknisi
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'task_id'   => 'required|integer',
            'latitude'  => 'required|string',
            'longitude' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error', 
                'errors' => $validator->errors()
            ], 422);
        }

        // Simpan data koordinat baru ke database
        $tracking = Tracking::create([
            'user_id'   => auth()->id(), // Otomatis ngambil ID user dari token login Sanctum
            'task_id'   => $request->task_id,
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json([
            'status' => 'success', 
            'message' => 'Koordinat live berhasil disimpan!', 
            'data' => $tracking
        ], 200);
    }

    // Fungsi untuk Web Admin (Ngambil 1 koordinat paling baru untuk ditaruh di peta)
    public function getLatestLocation($task_id)
    {
        $latestLocation = Tracking::where('task_id', $task_id)
                                  ->latest() // Urutkan dari yang paling baru masuk
                                  ->first(); // Ambil 1 baris teratas saja

        if (!$latestLocation) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Belum ada data tracking untuk tugas ini'
            ], 404);
        }

        return response()->json([
            'status' => 'success', 
            'data' => $latestLocation
        ], 200);
    }
}