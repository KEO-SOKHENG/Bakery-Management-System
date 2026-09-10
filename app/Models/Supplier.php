<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'status',
        'notes',
    ];

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function canDelete(): bool
    {
        return $this->purchaseOrders()->count() === 0 && $this->ingredients()->count() === 0;
    }

    public function totalPurchaseValue(): float
    {
        return (float) $this->purchaseOrders()
            ->where('status', 'received')
            ->sum('total_cost');
    }
}
