@extends('layouts.app')

@section('title', 'Product Categories - Bakery Management System')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/categories.css') }}">
@endpush

@section('content')
<div class="categories-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
    <div>
        <h2 style="font-size: 1.3rem; font-weight: 800; margin: 0;">Active Product Categories ({{ $categories->count() }})</h2>
        <p style="font-size: 0.825rem; color: #71717a; margin-top: 0.2rem;">Organize product catalogs into categories for POS display and inventory tracking</p>
    </div>
    <button class="btn btn-primary" id="btn_open_category_modal" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; background: #4d2c20; color: #fff; border-radius: 9999px; border: none; font-weight: 700; cursor: pointer;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <span>Add New Category</span>
    </button>
</div>

<div class="categories-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.25rem;">
    @forelse($categories as $cat)
        @php
            $catName = strtolower($cat->name ?? '');
            $catSvg = match(true) {
                str_contains($catName, 'viennoiserie') || str_contains($catName, 'pastr') => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#d97736" stroke-width="2"><path d="M6 13.8a4.5 4.5 0 1 1 2.6-8.3 5 5 0 0 1 6.8 0 4.5 4.5 0 1 1 2.6 8.3v4.2H6v-4.2z"/><path d="M6 18h12v2H6z"/></svg>',
                str_contains($catName, 'cake') || str_contains($catName, 'dessert') => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#d97736" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="M12 3v4"/></svg>',
                str_contains($catName, 'beverage') || str_contains($catName, 'coffee') => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#d97736" stroke-width="2"><path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V8z"/><line x1="6" y1="2" x2="6" y2="4"/><line x1="10" y1="2" x2="10" y2="4"/><line x1="14" y1="2" x2="14" y2="4"/></svg>',
                str_contains($catName, 'savory') || str_contains($catName, 'snack') => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#d97736" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M3 12h18"/></svg>',
                default => '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#d97736" stroke-width="2"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>',
            };
        @endphp
        <div class="category-card" style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem; background: var(--bg-card, #ffffff); border-radius: 1rem; border: 1px solid var(--border-subtle, #e4e4e7); position: relative;">
            <div class="category-icon" style="width: 52px; height: 52px; display: flex; align-items: center; justify-content: center; background: rgba(217, 119, 54, 0.1); border-radius: 0.75rem; border: 1px solid rgba(217, 119, 54, 0.2); flex-shrink: 0;">
                {!! $catSvg !!}
            </div>
            <div class="category-info" style="flex: 1; min-width: 0;">
                <h3 style="font-size: 1rem; font-weight: 800; margin: 0 0 0.25rem 0;">{{ $cat->name }}</h3>
                <p style="font-size: 0.8rem; color: #71717a; margin: 0;">{{ $cat->products_count ?? 0 }} Active Products</p>
            </div>
            <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Delete this category?');" style="position: absolute; top: 0.75rem; right: 0.75rem;">
                @csrf
                @method('DELETE')
                <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; opacity: 0.7; padding: 0.2rem;" title="Delete Category">
                    &times;
                </button>
            </form>
        </div>
    @empty
        <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 3rem;">
            No categories found in database. Click "Add New Category" to create one.
        </div>
    @endforelse
</div>

<!-- ADD CATEGORY MODAL -->
<div class="dash-modal-overlay" id="add_category_modal">
    <div class="dash-modal-content">
        <div class="dash-modal-header">
            <h3>Add New Product Category</h3>
            <button type="button" class="btn-close-modal" id="btn_close_cat_modal">&times;</button>
        </div>
        <form action="{{ route('admin.categories.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
            @csrf
            <div>
                <label class="form-label">Category Name</label>
                <input type="text" name="name" class="ios-input-field" placeholder="e.g. Artisanal Breads" required>
            </div>
            <div>
                <label class="form-label">Description</label>
                <textarea name="description" class="ios-textarea-field" rows="3" placeholder="Brief catalog description..."></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 0.5rem;">
                <button type="button" class="btn btn-secondary" id="btn_cancel_cat" style="padding: 0.6rem 1.25rem; border-radius: 9999px; cursor: pointer;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.5rem; background: #4d2c20; color: #fff; border-radius: 9999px; border: none; font-weight: 700; cursor: pointer;">Save Category</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('add_category_modal');
        const openBtn = document.getElementById('btn_open_category_modal');
        const closeBtn = document.getElementById('btn_close_cat_modal');
        const cancelBtn = document.getElementById('btn_cancel_cat');

        function openCategoryModal() {
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeCategoryModal() {
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        if (openBtn) openBtn.addEventListener('click', openCategoryModal);
        if (closeBtn) closeBtn.addEventListener('click', closeCategoryModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeCategoryModal);

        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeCategoryModal();
            });
        }
    });
</script>
@endpush
@endsection
