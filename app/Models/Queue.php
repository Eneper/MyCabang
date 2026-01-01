<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    use HasFactory;

    protected $table = 'queues';

    protected $fillable = [
        'user_id',
        'number',
        'status',
        'note',
        'served_at',
    ];

    protected $casts = [
        'served_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}