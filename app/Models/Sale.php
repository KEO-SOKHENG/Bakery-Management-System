<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'total',
        'payment_method',
        'payment_status',
        'sold_at',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
        'total' => 'decimal:2',
    ];


    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
