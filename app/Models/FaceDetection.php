<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaceDetection extends Model
{
    use HasFactory;

    protected $fillable = ['name','photo','metadata','customer_id','confirmed_at'];

    protected $casts = [
        'metadata' => 'array',
        'confirmed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
