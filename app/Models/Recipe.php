<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'description',
        'instructions',
        'production_time',
        'yield_quantity',
        'status',
    ];

    protected $casts = [
        'production_time' => 'integer',
        'yield_quantity' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredients')
                    ->withPivot('id', 'quantity', 'unit')
                    ->withTimestamps();
    }

    public function recipeIngredients()
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    /**
     * Total Recipe Cost = SUM(ingredient required quantity * ingredient purchase/cost price)
     */
    public function getTotalCostAttribute(): float
    {
        // Use loaded recipeIngredients with ingredient if available to prevent N+1
        $items = $this->relationLoaded('recipeIngredients')
            ? $this->recipeIngredients
            : $this->recipeIngredients()->with('ingredient')->get();

        $total = 0.0;
        foreach ($items as $ri) {
            $cost = $ri->ingredient ? (float) $ri->ingredient->cost : 0.0;
            $total += (float) $ri->quantity * $cost;
        }

        return round($total, 2);
    }

    /**
     * Cost Per Unit = Total Recipe Cost / yield_quantity
     */
    public function getCostPerUnitAttribute(): float
    {
        $yield = max(1, (int) ($this->yield_quantity ?? 1));
        return round($this->total_cost / $yield, 2);
    }

    /**
     * Formatted production time string (e.g. 45 mins, 1h 30m).
     */
    public function getFormattedProductionTimeAttribute(): string
    {
        if (empty($this->production_time) || $this->production_time <= 0) {
            return 'N/A';
        }

        if ($this->production_time < 60) {
            return "{$this->production_time} mins";
        }

        $hours = intdiv($this->production_time, 60);
        $minutes = $this->production_time % 60;

        return $minutes > 0 ? "{$hours}h {$minutes}m" : "{$hours}h";
    }

    public function getRecipeNameAttribute(): string
    {
        return $this->name ?? 'Recipe Formula';
    }

    public function getProductNameAttribute(): string
    {
        return $this->product ? $this->product->name : 'Bakery Product';
    }

    public function getPrepTimeAttribute(): string
    {
        return $this->formatted_production_time;
    }

    public function getBakeTempAttribute(): string
    {
        if ($this->instructions && preg_match('/(\d+\s*°?C)/i', $this->instructions, $matches)) {
            return $matches[1];
        }
        return '190°C';
    }

    public function getBakingInstructionsAttribute(): ?string
    {
        return $this->instructions;
    }

    public function getEstimatedCostAttribute(): float
    {
        return $this->cost_per_unit;
    }

    public function getIngredientsSummaryAttribute(): string
    {
        if ($this->ingredients->isNotEmpty()) {
            return $this->ingredients->map(function ($ing) {
                $qty = $ing->pivot ? (float) $ing->pivot->quantity : 0;
                $unit = $ing->pivot ? $ing->pivot->unit : $ing->unit;
                return "{$qty} {$unit} {$ing->name}";
            })->implode(', ');
        }
        return $this->description ?? 'Standard ingredients';
    }
}
