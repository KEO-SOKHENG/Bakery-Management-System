<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'loyalty_points',
        'loyalty_tier',
        'status',
    ];

    protected $casts = [
        'loyalty_points' => 'integer',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class)->orderBy('id', 'desc');
    }

    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Order::class);
    }

    public function sales()
    {
        return $this->hasManyThrough(Sale::class, Order::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Calculate loyalty tier based on earned points
     */
    public static function calculateTier(int $points): string
    {
        if ($points >= 500) return 'vip';
        if ($points >= 300) return 'gold';
        if ($points >= 100) return 'silver';
        return 'standard';
    }

    /**
     * Safely award loyalty points and update tier
     */
    public function addLoyaltyPoints(int $points): void
    {
        $this->loyalty_points = max(0, $this->loyalty_points + $points);
        $this->loyalty_tier = self::calculateTier($this->loyalty_points);
        $this->save();
    }

    /**
     * Lifetime spend across completed orders
     */
    public function getTotalSpentAttribute(): float
    {
        return (float) $this->orders()->where('order_status', 'completed')->sum('total');
    }

    /**
     * Total number of orders placed
     */
    public function getTotalOrdersCountAttribute(): int
    {
        return $this->orders()->count();
    }

    /**
     * Average spending per order
     */
    public function getAverageOrderValueAttribute(): float
    {
        $count = $this->orders()->where('order_status', 'completed')->count();
        if ($count === 0) return 0.0;
        return round($this->total_spent / $count, 2);
    }

    /**
     * Date of most recent order
     */
    public function getLastVisitAttribute()
    {
        $latest = $this->orders()->latest('created_at')->first();
        return $latest ? $latest->created_at : null;
    }
}

