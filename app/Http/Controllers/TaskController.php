<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    // 1. Ambil Semua Data Tugas (Dipakai Admin/Teknisi/Customer)
    public function index()
    {
        $tasks = Task::with('technician')->orderBy('created_at', 'desc')->get();
        return response()->json($tasks, 200);
    }

    // 2. Buat Tugas Baru (Ditembak dari APK Customer)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name'      => 'required|string',
            'device_name'        => 'required|string',
            'damage_description' => 'required|string',
            'technician_id'      => 'required',
            'latitude'           => 'required|string',
            'longitude'          => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $distance = null;

        // Jembatan nembak ke Python FastAPI untuk hitung jarak otomatis
        try {
            $response = Http::post('http://127.0.0.1:8000/api/hitung-jarak', [
                'latitude'  => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            if ($response->successful()) {
                $distance = $response->json()['distance_km'] ?? null;
            }
        } catch (\Exception $e) {
            // Jika Python mati, biarkan distance bernilai null dulu agar aplikasi tidak crash
            $distance = null;
        }

        // Simpan data ke database MySQL sesuai kolom migration lu
        $task = Task::create([
            'customer_name'      => $request->customer_name,
            'device_name'        => $request->device_name,
            'damage_description' => $request->damage_description,
            'technician_id'      => $request->technician_id,
            'latitude'           => $request->latitude,
            'longitude'          => $request->longitude,
            'distance_km'        => $distance,
            'status'             => 'pending', // Default awal dari migration lu
            'cost'               => 0,         // Default awal 0 rupiah
        ]);

        return response()->json([
            'message' => 'Tugas berhasil dibuat dan Jarak dihitung otomatis oleh Python!',
            'data'    => $task
        ], 201);
    }

    // 3. Update Status Tugas & Biaya (Ditembak dari APK Teknisi)
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,done', // Sesuai enum di migration lu
            'cost'   => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Tugas tidak ditemukan!'], 404);
        }

        // Jalankan update data
        $task->status = $request->status;
        if ($request->has('cost')) {
            $task->cost = $request->cost;
        }
        $task->save();

        return response()->json([
            'message' => 'Status tugas berhasil diperbarui oleh teknisi!',
            'data'    => $task
        ], 200);
    }
}