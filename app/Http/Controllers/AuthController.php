<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validasi diperlonggar agar tidak langsung memblokir jika key bertabrakan format
        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        // AMBIL DATA DENGAN MENCOCOKKAN HTML IONIC (CamelCase & SnakeCase dicover semua)
        $email         = $request->input('email') ?? $request->input('Email') ?? null;
        $phone         = $request->input('phone') ?? $request->input('nomor_telepon') ?? '-';
        $tempat_lahir  = $request->input('tempatLahir') ?? $request->input('tempat_lahir') ?? '-';
        $tanggal_lahir = $request->input('tanggal_lahir') ?? $request->input('tanggalLahir') ?? date('Y-m-d');
        $nik           = $request->input('nik') ?? $request->input('NIK') ?? '-';
        $alamat        = $request->input('alamat') ?? $request->input('alamat_lengkap') ?? '-';

        // VALIDASI EMAIL MANUAL (Supaya pesannya jelas kalau email beneran gak dikirim dari TS)
        if (empty($email)) {
            return response()->json([
                'success' => false,
                'errors'  => ['email' => ['Data email tidak terkirim dari aplikasi Ionic, periksa payload di register.page.ts lu Mad!']]
            ], 422);
        }

        // Proteksi jika email duplikat di database
        $userExists = User::where('email', $email)->exists();
        if ($userExists) {
            return response()->json([
                'success' => false,
                'errors'  => ['email' => ['Email ini sudah terdaftar di database, silahkan gunakan email lain.']]
            ], 422);
        }

        // Eksekusi Simpan Langsung ke Kolom Database
        $user = new User();
        $user->name         = $request->input('name');
        $user->email        = $email;
        $user->password     = Hash::make($request->input('password'));
        $user->phone        = strval($phone);
        $user->tempat_lahir = $tempat_lahir;
        $user->tanggal_lahir= $tanggal_lahir;
        $user->nik          = strval($nik);
        $user->alamat       = $alamat;
        $user->role         = $request->input('role') ?? 'teknisi';
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success'      => true,
            'message'      => 'Akun baru berhasil didaftarkan!',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'data'         => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success'      => true,
            'message'      => 'Login berhasil!',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'role'         => $user->role, 
            'user'         => $user
        ], 200);
    }
}