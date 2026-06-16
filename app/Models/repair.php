<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repair extends Model
{
    use HasFactory;

    protected $table = 'repairs';

    protected $fillable = [
        'user_id',
        'customer_name',
        'device_name',
        'damage_description',
        'status',
        'cost',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}