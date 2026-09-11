@extends('layouts.app')

@section('title', 'Products Management - Bakery System')

@section('content')
<div class="products-header-bar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
    <div>
        <h2 style="font-size: 1.3rem; font-weight: 800; margin: 0; color: #5D4037;">Products Catalog</h2>
        <p style="font-size: 0.825rem; color: #71717a; margin-top: 0.2rem;">Manage products, pricing, categories, and stock quantities</p>
    </div>
    <button class="btn btn-primary" id="btn_open_create_product_modal" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; background: #5D4037; color: #fff; border-radius: 9999px; border: none; font-weight: 700; cursor: pointer;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <span>Add New Product</span>
    </button>
</div>

@if(session('success'))
    <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; padding: 0.85rem 1.15rem; border-radius: 8px; margin-bottom: 1.25rem; font-weight: 600;">
        {{ session('success') }}
    </div>
@endif

<!-- Products Card Grid -->
<div class="products-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.25rem;">
    @forelse($products as $product)
        <div class="product-card" id="product_card_{{ $product->id }}" style="background: #fff; border-radius: 12px; border: 1px solid #e4e4e7; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); position: relative;">
            <div style="font-size: 3rem; text-align: center; margin-bottom: 0.75rem;">{{ $product->image_emoji ?? '🥖' }}</div>
            <div style="font-weight: 800; font-size: 1.05rem; color: #27272a; text-align: center;">{{ $product->name }}</div>
            <div style="font-size: 0.8rem; color: #71717a; text-align: center; margin-top: 0.2rem;">
                SKU: {{ $product->sku }} | Category: {{ $product->category->name ?? 'Uncategorized' }}
            </div>
            <div style="font-size: 0.825rem; color: #52525b; margin-top: 0.5rem; text-align: center;">
                {{ $product->description ?: 'No description' }}
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid #f4f4f5;">
                <span style="font-weight: 800; color: #16a34a; font-size: 1.1rem;">${{ number_format($product->price, 2) }}</span>
                <span style="padding: 0.25rem 0.6rem; border-radius: 9999px; font-weight: 700; font-size: 0.75rem; background: {{ $product->stock <= $product->minimum_stock ? '#fee2e2' : '#dcfce7' }}; color: {{ $product->stock <= $product->minimum_stock ? '#991b1b' : '#166534' }};">
                    Stock: {{ $product->stock }}
                </span>
            </div>
            @if($product->shelf_life || $product->expiry_date)
                <div style="display: flex; justify-content: space-between; font-size: 0.775rem; color: #71717a; margin-top: 0.4rem; padding: 0.25rem 0.5rem; background: #fafafa; border-radius: 6px;">
                    @if($product->shelf_life)
                        <span>Shelf Life: <strong>{{ $product->shelf_life }} days</strong></span>
                    @endif
                    @if($product->expiry_date)
                        <span style="{{ $product->isExpired() ? 'color: #dc2626; font-weight: 800;' : ($product->isExpiringSoon() ? 'color: #d97706; font-weight: 700;' : '') }}">
                            Exp: {{ $product->expiry_date->format('M d, Y') }}
                        </span>
                    @endif
                </div>
            @endif
            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.75rem;">
                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Delete product?');" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 0.8rem; font-weight: 700;">Delete</button>
                </form>
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 3rem;">
            No products found in PostgreSQL database. Click "Add New Product" to create one!
        </div>
    @endforelse
</div>

<!-- ADD NEW PRODUCT MODAL DIALOG -->
<div class="product-modal-overlay" id="create_product_modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; align-items: center; justify-content: center;">
    <div class="product-modal-content" style="background: #fff; padding: 1.5rem; border-radius: 12px; width: 100%; max-width: 500px;">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: #5D4037;">Add New Bakery Product</h3>
            <button type="button" class="btn-close-modal" id="btn_close_product_modal" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <form id="form_create_product" action="{{ route('admin.products.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
            @csrf
            <div>
                <label style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem; color: #27272a;">Category</label>
                <select name="category_id" style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #d4d4d8; background: #fff;">
                    <option value="">Select Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem; color: #27272a;">Product Name</label>
                <input type="text" name="name" placeholder="e.g. Chocolate Cake" required style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #d4d4d8;">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem; color: #27272a;">Price ($)</label>
                    <input type="number" step="0.01" name="price" placeholder="0.00" required style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #d4d4d8;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem; color: #27272a;">Cost ($)</label>
                    <input type="number" step="0.01" name="cost" placeholder="0.00" style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #d4d4d8;">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem; color: #27272a;">Stock Quantity</label>
                    <input type="number" name="stock" value="10" required style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #d4d4d8;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem; color: #27272a;">Minimum Stock Alert</label>
                    <input type="number" name="minimum_stock" value="5" style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #d4d4d8;">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem; color: #27272a;">Shelf Life (Days)</label>
                    <input type="number" name="shelf_life" min="1" placeholder="e.g. 3" style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #d4d4d8;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem; color: #27272a;">Batch Expiry Date</label>
                    <input type="date" name="expiry_date" style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #d4d4d8;">
                </div>
            </div>
            <div>
                <label style="display: block; font-weight: 700; font-size: 0.825rem; margin-bottom: 0.35rem; color: #27272a;">Description</label>
                <textarea name="description" rows="2" placeholder="Brief description..." style="width: 100%; padding: 0.65rem; border-radius: 6px; border: 1px solid #d4d4d8;"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 0.5rem;">
                <button type="button" id="btn_cancel_product_modal" style="padding: 0.6rem 1.25rem; border-radius: 9999px; border: 1px solid #d4d4d8; background: #fff; cursor: pointer; font-weight: 700;">Cancel</button>
                <button type="submit" style="padding: 0.6rem 1.5rem; background: #5D4037; color: #fff; border-radius: 9999px; border: none; font-weight: 700; cursor: pointer;">Save Product</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('create_product_modal');
        const openBtn = document.getElementById('btn_open_create_product_modal');
        const closeBtn = document.getElementById('btn_close_product_modal');
        const cancelBtn = document.getElementById('btn_cancel_product_modal');

        if (openBtn && modal) {
            openBtn.addEventListener('click', () => modal.style.display = 'flex');
        }
        [closeBtn, cancelBtn].forEach(btn => {
            if (btn && modal) btn.addEventListener('click', () => modal.style.display = 'none');
        });
    });
</script>
@endpush
@endsection
