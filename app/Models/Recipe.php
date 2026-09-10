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
        'status',
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
}
