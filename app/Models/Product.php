<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'description',
        'price',
        'cost',
        'stock',
        'minimum_stock',
        'shelf_life',
        'expiry_date',
        'status',
        'image_emoji',
        'image_path',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'stock' => 'integer',
        'minimum_stock' => 'integer',
        'shelf_life' => 'integer',
        'expiry_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function recipe()
    {
        return $this->hasOne(Recipe::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->minimum_stock;
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 3): bool
    {
        if (!$this->expiry_date || $this->isExpired()) {
            return false;
        }
        return $this->expiry_date->lte(now()->addDays($days));
    }
}
