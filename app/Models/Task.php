<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'device_name',
        'damage_description',
        'technician_id',
        'status',
        'cost',
        'image_proof',
        'latitude',
        'longitude',
        'distance_km'
    ];

    // Relasi ke tabel Users (Biar tahu ini tugas punyanya teknisi siapa)
    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}