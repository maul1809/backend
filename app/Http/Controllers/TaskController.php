<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    // 1. LIHAT SEMUA TUGAS AKTIF (Dipakai Web Admin Monitoring & Mobile Teknisi - Tugas yang belum beres)
    public function index()
    {
        // Menyaring hanya menampilkan tugas yang aktif berjalan sesuai workflow
        $tasks = Task::with('technician')
            ->whereIn('status', ['assigned', 'accepted', 'on-going'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($tasks, 200);
    }

    // 2. CREATE & ASSIGN TASK (Ditembak dari Web Dashboard Admin - SRS Bab 3.2)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name'      => 'required|string',
            'device_name'        => 'required|string',
            'damage_description' => 'required|string',
            'technician_id'      => 'required|integer', 
            'latitude'           => 'required|string',
            'longitude'          => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $distance = null;

        // Jembatan otomatis nodong hitung jarak ke Python Cloud Hugging Face
        try {
            $response = Http::post('https://maul1809-hitung-jarak-elektronikcare.hf.space/api/hitung-jarak', [
                'latitude'  => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            if ($response->successful()) {
                $distance = $response->json()['distance_km'] ?? null;
            }
        } catch (\Exception $e) {
            $distance = null; // Pengaman jika Python down
        }

        // Simpan ke database dengan status awal 'assigned' sesuai standard FSM
        $task = Task::create([
            'customer_name'      => $request->customer_name,
            'device_name'        => $request->device_name,
            'damage_description' => $request->damage_description,
            'technician_id'      => $request->technician_id,
            'latitude'           => $request->latitude,
            'longitude'          => $request->longitude,
            'distance_km'        => $distance,
            'status'             => 'assigned', // Sesuai Ketentuan BRD & SRS
            'cost'               => 0,
            'image_proof'        => null, // Sinkron dengan nama kolom di migration
        ]);

        return response()->json([
            'message' => 'Tugas sukses dibuat oleh Admin dan di-assign ke Teknisi!',
            'data'    => $task
        ], 201);
    }

    // 3. WORKFLOW STATUS FLOW & UPLOAD BUKTI (Ditembak dari APK Mobile Teknisi - SRS Bab 5)
    public function updateStatus(Request $request, $id)
    {
        // Validasi status wajib sesuai alur dokumen: assigned -> accepted/rejected -> on-going -> completed
        $validator = Validator::make($request->all(), [
            'status'      => 'required|in:assigned,accepted,rejected,on-going,completed',
            'cost'        => 'nullable|integer',
            'proof_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Validasi inputan file foto dari frontend
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Tugas tidak ditemukan!'], 404);
        }

        // Update status operasional
        $task->status = $request->status;

        // Jika ada inputan biaya dari teknisi
        if ($request->has('cost')) {
            $task->cost = $request->cost;
        }

        // JIKA STATUS COMPLETED, WAJIB SIMPAN FOTO BUKTI PEKERJAAN (Standard Dokumen TB)
        if ($request->status == 'completed') {
            if ($request->hasFile('proof_image')) {
                $file = $request->file('proof_image');
                $path = $file->store('proofs', 'public'); // Tersimpan di folder storage/app/public/proofs
                
                // Menyimpan path ke kolom image_proof sesuai skema database
                $task->image_proof = Storage::url($path);
            } else {
                return response()->json(['message' => 'Gagal! Status completed wajib mengunggah foto bukti pekerjaan.'], 422);
            }
        }

        $task->save();

        return response()->json([
            'message' => 'Status alur kerja operasional berhasil diperbarui!',
            'data'    => $task
        ], 200);
    }

    // 4. HISTORI PEKERJAAN (Sesuai BRD Bab 9.1 - Menampilkan data masa lalu yang sudah kelar/ditolak)
    public function history()
    {
        $history = Task::with('technician')
            ->whereIn('status', ['completed', 'rejected'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($history, 200);
    }
}