<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Production extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_number',
        'recipe_id',
        'product_id',
        'quantity',
        'production_date',
        'scheduled_at',
        'started_at',
        'completed_at',
        'status',
        'notes',
        'user_id',
        'baker_id',
    ];

    protected function casts(): array
    {
        return [
            'production_date' => 'datetime',
            'scheduled_at'    => 'datetime',
            'started_at'      => 'datetime',
            'completed_at'    => 'datetime',
            'quantity'        => 'integer',
        ];
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function baker()
    {
        return $this->belongsTo(User::class, 'baker_id');
    }

    public function isScheduled(): bool
    {
        return strtolower($this->status) === 'scheduled';
    }

    public function isInProgress(): bool
    {
        return strtolower($this->status) === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return strtolower($this->status) === 'completed';
    }

    public function isCancelled(): bool
    {
        return strtolower($this->status) === 'cancelled';
    }

    /**
     * Server-side workflow validator for state transitions.
     * Allowed:
     * - scheduled -> in_progress, completed, cancelled
     * - in_progress -> completed, cancelled
     * - completed -> final state (none allowed)
     * - cancelled -> final state (none allowed)
     */
    public function canTransitionTo(string $targetStatus): bool
    {
        $current = strtolower($this->status);
        $target = strtolower($targetStatus);

        if ($current === $target) {
            return false;
        }

        return match ($current) {
            'scheduled'   => in_array($target, ['in_progress', 'completed', 'cancelled']),
            'in_progress' => in_array($target, ['completed', 'cancelled']),
            'completed'   => false, // Immutable final state
            'cancelled'   => false, // Immutable final state
            default       => false,
        };
    }

    /**
     * Calculate human-readable duration between start and completion.
     */
    public function getDurationAttribute(): ?string
    {
        if ($this->started_at && $this->completed_at) {
            $diffMins = $this->started_at->diffInMinutes($this->completed_at);
            if ($diffMins < 60) {
                return $diffMins . ' ' . ($diffMins === 1 ? 'min' : 'mins');
            }
            $hours = floor($diffMins / 60);
            $mins = $diffMins % 60;
            return $hours . 'h ' . ($mins > 0 ? $mins . 'm' : '');
        }
        return null;
    }

    /**
     * Get computed required ingredients based on recipe and batch quantity.
     */
    public function getRequiredIngredients()
    {
        if (!$this->recipe || !$this->recipe->relationLoaded('ingredients')) {
            $this->loadMissing('recipe.ingredients');
        }

        if (!$this->recipe) {
            return collect();
        }

        return $this->recipe->ingredients->map(function ($ingredient) {
            $unitQty = (float) $ingredient->pivot->quantity;
            $requiredTotal = round($unitQty * $this->quantity, 2);
            $currentStock = (float) $ingredient->quantity;

            return (object) [
                'id'             => $ingredient->id,
                'name'           => $ingredient->name,
                'unit'           => $ingredient->unit ?? $ingredient->pivot->unit,
                'unit_quantity'  => $unitQty,
                'required_total' => $requiredTotal,
                'current_stock'  => $currentStock,
                'is_sufficient'  => $currentStock >= $requiredTotal,
                'shortage'       => max(0, round($requiredTotal - $currentStock, 2)),
            ];
        });
    }
}
