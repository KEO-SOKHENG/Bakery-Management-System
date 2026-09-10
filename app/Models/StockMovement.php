<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'ingredient_id',
        'type', // 'in', 'out'
        'quantity',
        'unit',
        'unit_cost',
        'reference_type', // 'purchase_order', 'production', 'production_cancellation', 'manual_adjustment'
        'reference_id',
        'reference_number',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
