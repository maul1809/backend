<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Repair;

class RepairController extends Controller
{
    // 1. Ambil Semua Data Servis
    public function index()
    {
        $repairs = Repair::with('user')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar perbaikan berhasil diambil.',
            'data' => $repairs
        ], 200);
    }

    // 2. Tambah Data Servis Baru
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'device_name' => 'required|string',
            'damage_description' => 'required|string',
        ]);

        $repair = Repair::create([
            'user_id' => $request->user()->id,
            'customer_name' => $request->customer_name,
            'device_name' => $request->device_name,
            'damage_description' => $request->damage_description,
            'status' => 'pending',
            'cost' => 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data perbaikan berhasil ditambahkan.',
            'data' => $repair
        ], 201);
    }

    // 3. Update Status dan Biaya (Fitur yang bikin kamu stuck)
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,process,done,cancelled',
            'cost' => 'required|integer',
        ]);

        $repair = Repair::find($id);

        if (!$repair) {
            return response()->json([
                'success' => false,
                'message' => 'Data perbaikan tidak ditemukan.'
            ], 404);
        }

        $repair->update([
            'status' => $request->status,
            'cost' => $request->cost,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data perbaikan berhasil diperbarui.',
            'data' => $repair
        ], 200);
    }

    // 4. Hapus Data Servis
    public function destroy($id)
    {
        $repair = Repair::find($id);

        if (!$repair) {
            return response()->json([
                'success' => false,
                'message' => 'Data perbaikan tidak ditemukan.'
            ], 404);
        }

        $repair->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data perbaikan berhasil dihapus.'
        ], 200);
    }
}