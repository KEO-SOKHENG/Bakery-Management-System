@extends('layouts.app')

@section('title', 'Bakery Formulas & Recipes - Bakery System')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/recipes.css') }}">
@endpush

@section('content')
<x-page-header title="Bakery Recipes" subtitle="Standardized recipes, ingredients, baking temperatures, and yield costs">
    <x-slot:actions>
        <x-button variant="primary" id="btn_open_recipe_modal">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M6 6h10"/><path d="M6 10h10"/></svg>
            <span>Add Recipe</span>
        </x-button>
    </x-slot:actions>
</x-page-header>

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
                    <div class="recipe-section-heading">Ingredients:</div>
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

                <form action="{{ route('admin.recipes.destroy', $recipe->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete recipe \'{{ $recipe->recipe_name ?? 'Item' }}\'?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-icon-action delete" title="Delete Recipe">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            <line x1="10" y1="11" x2="10" y2="17"></line>
                            <line x1="14" y1="11" x2="14" y2="17"></line>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 3rem;">
            No recipes found. Click "Add Recipe" to create one.
        </div>
    @endforelse
</div>

<!-- ADD RECIPE MODAL -->
<x-modal id="add_recipe_modal" title="Add Recipe" maxWidth="540px" class="dash-modal-overlay">
    <form action="{{ route('admin.recipes.store') }}" method="POST">
        @csrf
        <x-input name="name" label="Recipe Name" placeholder="e.g. Classic Butter Croissant" :required="true" />
        <x-select name="product_id" label="Bakery Product" :required="true">
            @foreach($products as $p)
                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->category ? $p->category->name : 'Bakery' }})</option>
            @endforeach
        </x-select>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem;">
            <x-input type="number" name="yield_quantity" label="Batch Yield (pcs)" value="1" min="1" placeholder="50" :required="true" />
            <x-input type="number" name="production_time" label="Baking Time (mins)" min="1" placeholder="45" :required="true" />
            <x-input name="bake_temp" label="Bake Temp" placeholder="210°C" />
        </div>
        <div class="form-group">
            <label class="form-label">Ingredients</label>
            <textarea name="description" class="form-control-textarea" rows="2" placeholder="e.g. 10kg Flour, 6.5kg Butter, 500g Sugar, 250g Yeast, 180g Salt"></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Baking Notes</label>
            <textarea name="instructions" class="form-control-textarea" rows="2" placeholder="Steps, dough folding notes, and oven temperature settings..."></textarea>
        </div>
        <div class="modal-footer" style="padding-top: 1rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
            <x-button variant="secondary" type="button" id="btn_cancel_recipe">Cancel</x-button>
            <x-button variant="primary" type="submit">Save Recipe</x-button>
        </div>
    </form>
</x-modal>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('add_recipe_modal');
        const openBtn = document.getElementById('btn_open_recipe_modal');
        const closeBtn = document.getElementById('btn_close_recipe_modal');
        const cancelBtn = document.getElementById('btn_cancel_recipe');

        function openModal() {
            if (modal) {
                modal.classList.add('active', 'is-active');
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeModal() {
            if (modal) {
                modal.classList.remove('active', 'is-active');
                modal.style.display = 'none';
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
