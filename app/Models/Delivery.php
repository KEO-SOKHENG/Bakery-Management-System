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

    public const STATUS_PENDING = 'pending';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($delivery) {
            if (empty($delivery->tracking_number)) {
                $delivery->tracking_number = 'DEL-' . strtoupper(substr(uniqid(), -8));
            }
        });
    }

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

    public function isPending(): bool
    {
        return $this->delivery_status === self::STATUS_PENDING;
    }

    public function isAssigned(): bool
    {
        return $this->delivery_status === self::STATUS_ASSIGNED;
    }

    public function isOutForDelivery(): bool
    {
        return $this->delivery_status === self::STATUS_OUT_FOR_DELIVERY;
    }

    public function isDelivered(): bool
    {
        return $this->delivery_status === self::STATUS_DELIVERED;
    }

    public function isCancelled(): bool
    {
        return $this->delivery_status === self::STATUS_CANCELLED;
    }
}
