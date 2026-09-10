<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'order_date',
        'expected_delivery_date',
        'received_date',
        'status',
        'total_cost',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'received_date' => 'datetime',
        'total_cost' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isOrdered(): bool
    {
        return $this->status === 'ordered';
    }

    public function isReceived(): bool
    {
        return $this->status === 'received';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canTransitionTo(string $targetStatus): bool
    {
        $target = strtolower($targetStatus);
        $current = strtolower($this->status);

        if ($current === 'draft') {
            return in_array($target, ['ordered', 'cancelled']);
        }

        if ($current === 'ordered') {
            return in_array($target, ['received', 'cancelled']);
        }

        // Received and Cancelled are terminal states
        return false;
    }

    public function recalculateTotal(): void
    {
        $this->total_cost = $this->items()->sum('subtotal');
        $this->save();
    }
}
