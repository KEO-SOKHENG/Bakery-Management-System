@extends('layouts.app')

@section('title', 'Bakery Formulas & Recipes - Bakery System')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/recipes.css') }}">
@endpush

@section('content')
<div class="recipes-header-bar">
    <div>
        <h2 style="font-size: 1.3rem; font-weight: 800; margin: 0;">Bakery Formulas & Recipes ({{ $recipes->count() }})</h2>
        <p style="font-size: 0.825rem; color: #71717a; margin-top: 0.2rem;">Standardized dough formulas, ingredient quantities, baking temperatures, and yield batch costs</p>
    </div>
    <button class="btn btn-primary" id="btn_open_recipe_modal" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.25rem; background: #4d2c20; color: #fff; border-radius: 9999px; border: none; font-weight: 700; cursor: pointer;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M6 6h10"/><path d="M6 10h10"/></svg>
        <span>Add New Recipe</span>
    </button>
</div>

<div class="recipes-grid">
    @forelse($recipes as $recipe)
        <div class="recipe-card">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                    <div>
                        <h3 class="recipe-title">{{ $recipe->recipe_name }}</h3>
                        <span class="recipe-product-tag">{{ $recipe->product_name }}</span>
                    </div>
                </div>

                <div class="recipe-meta-box">
                    <div class="recipe-meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span title="Estimated Production Time">Time: {{ $recipe->formatted_production_time }}</span>
                    </div>
                    <div class="recipe-meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
                        <span>Bake: {{ $recipe->bake_temp }}</span>
                    </div>
                    <div class="recipe-meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                        <span>Yield: {{ $recipe->yield_quantity }} pcs</span>
                    </div>
                </div>

                <div>
                    <div class="recipe-section-heading">Required Ingredient Formula:</div>
                    <p class="recipe-ingredients-box">{{ $recipe->ingredients_summary }}</p>
                </div>

                @if($recipe->baking_instructions)
                    <div>
                        <div class="recipe-section-heading">Baking Notes:</div>
                        <p class="recipe-notes-text">{{ $recipe->baking_instructions }}</p>
                    </div>
                @endif
            </div>

            <div class="recipe-card-footer" style="display: flex; justify-content: space-between; align-items: center; gap: 0.75rem;">
                <div>
                    <span class="recipe-cost-label" data-lang-key="cost_per_unit">Cost Per Unit</span>
                    <span class="recipe-cost-value">${{ number_format($recipe->cost_per_unit, 2) }}</span>
                </div>
                <div>
                    <span class="recipe-cost-label" data-lang-key="total_recipe_cost">Total Cost</span>
                    <span class="recipe-cost-value" style="color: #563020;">${{ number_format($recipe->total_cost, 2) }}</span>
                </div>

                <form action="{{ route('admin.recipes.destroy', $recipe->id) }}" method="POST" onsubmit="return confirm('Delete this recipe formula?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 0.8rem; font-weight: 700;">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 3rem;">
            No bakery recipes in database. Click "Add New Recipe" to create standard product formulas.
        </div>
    @endforelse
</div>

<!-- ADD RECIPE MODAL -->
<div class="dash-modal-overlay" id="add_recipe_modal">
    <div class="dash-modal-content">
        <div class="dash-modal-header">
            <h3>Add Bakery Product Recipe Formula</h3>
            <button type="button" class="btn-close-modal" id="btn_close_recipe_modal">&times;</button>
        </div>
        <form action="{{ route('admin.recipes.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
            @csrf
            <div>
                <label class="form-label">Recipe Formula Name</label>
                <input type="text" name="name" class="ios-input-field" placeholder="e.g. Classic Butter Croissant Formula" required>
            </div>
            <div>
                <label class="form-label">Target Bakery Product</label>
                <select name="product_id" class="ios-select-field" required>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->category ? $p->category->name : 'Bakery' }})</option>
                    @endforeach
                </select>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem;">
                <div>
                    <label class="form-label">Batch Yield (pcs)</label>
                    <input type="number" name="yield_quantity" class="ios-input-field" value="1" min="1" placeholder="50" required>
                </div>
                <div>
                    <label class="form-label">Prod. Time (mins)</label>
                    <input type="number" name="production_time" class="ios-input-field" min="1" placeholder="45" required>
                </div>
                <div>
                    <label class="form-label">Bake Temp</label>
                    <input type="text" name="bake_temp" class="ios-input-field" placeholder="210°C">
                </div>
            </div>
            <div>
                <label class="form-label">Ingredients Formula Summary</label>
                <textarea name="description" class="ios-textarea-field" rows="2" placeholder="e.g. 10kg T55 Flour, 6.5kg Normandy Butter, 500g Sugar, 250g Yeast, 180g Salt"></textarea>
            </div>
            <div>
                <label class="form-label">Baking Instructions & Notes</label>
                <textarea name="instructions" class="ios-textarea-field" rows="2" placeholder="Fermentation steps, dough lamination notes, and oven humidity settings..."></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 0.5rem;">
                <button type="button" class="btn btn-secondary" id="btn_cancel_recipe" style="border-radius: 9999px; cursor: pointer;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background: #4d2c20; color: #fff; border-radius: 9999px; border: none; font-weight: 700; cursor: pointer; padding: 0.65rem 1.5rem;">Save Recipe Formula</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('add_recipe_modal');
        const openBtn = document.getElementById('btn_open_recipe_modal');
        const closeBtn = document.getElementById('btn_close_recipe_modal');
        const cancelBtn = document.getElementById('btn_cancel_recipe');

        function openModal() {
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeModal() {
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        if (openBtn) openBtn.addEventListener('click', openModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeModal();
            });
        }
    });
</script>
@endpush
@endsection
