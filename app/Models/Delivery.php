<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'recipient_name',
        'recipient_phone',
        'delivery_address',
        'delivery_status',
        'scheduled_at',
        'delivered_at',
        'tracking_number',
        'delivery_fee',
        'notes',
    ];

    protected $casts = [
        'delivery_fee' => 'decimal:2',
        'scheduled_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function deliveryStaff()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
