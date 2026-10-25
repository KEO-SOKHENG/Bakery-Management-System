@extends('layouts.app')

@section('title', 'Products Management - Bakery System')

@section('header_toolbar')
    <div class="period-pill-group">
        <a href="{{ route('admin.products', array_merge(request()->except('category_id', 'page'), [])) }}"
           class="period-pill {{ !request('category_id') ? 'active' : '' }}">
            <span>All Categories</span>
        </a>
        @foreach($categories as $category)
            <a href="{{ route('admin.products', array_merge(request()->except('category_id', 'page'), ['category_id' => $category->id])) }}"
               class="period-pill {{ (string)request('category_id') === (string)$category->id ? 'active' : '' }}">
                <span>{{ $category->name }}</span>
            </a>
        @endforeach
    </div>
@endsection

@section('header_sub_action')
    <x-button variant="primary" id="btn_open_create_product_modal" class="sticky-add-product-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <span>Add Product</span>
    </x-button>
@endsection

@section('content')
@if(session('success'))
    <x-alert type="success" :message="session('success')" />
@endif

<!-- Products Card Grid -->
<div class="products-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.25rem;">
    @forelse($products as $product)
        <div class="product-card" id="product_card_{{ $product->id }}" style="background: #fff; border-radius: 12px; border: 1px solid #e4e4e7; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); position: relative;">
            @php $theme = $product->getIconTheme(); @endphp
            <div style="display: flex; justify-content: center; margin-bottom: 0.85rem;">
                <div style="width: 64px; height: 64px; border-radius: 16px; background: {{ $theme['bg'] }}; border: 1.5px solid {{ $theme['border'] }}; display: flex; align-items: center; justify-content: center; color: {{ $theme['color'] }}; box-shadow: 0 4px 12px {{ $theme['shadow'] }};">
                    {!! $product->getIconSvg(32) !!}
                </div>
            </div>
            <div style="font-weight: 800; font-size: 1.05rem; color: #27272a; text-align: center;">{{ $product->name }}</div>
            <div style="font-size: 0.8rem; color: #71717a; text-align: center; margin-top: 0.2rem;">
                SKU: {{ $product->sku }} | Category: {{ $product->category->name ?? 'Uncategorized' }}
            </div>
            <div style="font-size: 0.825rem; color: #52525b; margin-top: 0.5rem; text-align: center;">
                {{ $product->description ?: 'No description' }}
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid #f4f4f5;">
                <span style="font-weight: 800; color: #16a34a; font-size: 1.1rem;">${{ number_format($product->price, 2) }}</span>
                <x-badge :variant="$product->stock <= $product->minimum_stock ? 'danger' : 'success'">
                    Stock: {{ $product->stock }}
                </x-badge>
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
                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete product \'{{ $product->name }}\'?');" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-icon-action delete" title="Delete Product">
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
            No products found. Click "Add Product" to create one.
        </div>
    @endforelse
</div>

@if($products->hasPages())
    <div style="margin-top: 1.5rem; display: flex; justify-content: center;">
        {{ $products->links() }}
    </div>
@endif

<!-- ADD NEW PRODUCT MODAL DIALOG -->
<x-modal id="create_product_modal" title="Add Product" maxWidth="520px" class="product-modal-overlay">
    <form id="form_create_product" action="{{ route('admin.products.store') }}" method="POST">
        @csrf
        <x-select name="category_id" label="Category">
            <option value="">Select Category</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </x-select>
        <x-input name="name" label="Product Name" placeholder="e.g. Chocolate Cake" :required="true" />
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
            <x-input type="number" step="0.01" name="price" label="Price ($)" placeholder="0.00" :required="true" />
            <x-input type="number" step="0.01" name="cost" label="Cost ($)" placeholder="0.00" />
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
            <x-input type="number" name="stock" label="Stock Quantity" value="10" :required="true" />
            <x-input type="number" name="minimum_stock" label="Minimum Stock Alert" value="5" />
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;">
            <x-input type="number" name="shelf_life" label="Shelf Life (Days)" min="1" placeholder="e.g. 3" />
            <x-input type="date" name="expiry_date" label="Batch Expiry Date" />
        </div>
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" rows="2" class="form-control-textarea" placeholder="Brief description..."></textarea>
        </div>
        <div class="modal-footer" style="padding-top: 1rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
            <x-button variant="secondary" type="button" id="btn_cancel_product_modal">Cancel</x-button>
            <x-button variant="primary" type="submit">Save Product</x-button>
        </div>
    </form>
</x-modal>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('create_product_modal');
        const openBtn = document.getElementById('btn_open_create_product_modal');
        const closeBtn = document.getElementById('btn_close_product_modal');
        const cancelBtn = document.getElementById('btn_cancel_product_modal');

        if (openBtn && modal) {
            openBtn.addEventListener('click', () => {
                modal.classList.add('is-active');
                modal.style.display = 'flex';
            });
        }
        [closeBtn, cancelBtn].forEach(btn => {
            if (btn && modal) {
                btn.addEventListener('click', () => {
                    modal.classList.remove('is-active');
                    modal.style.display = 'none';
                });
            }
        });
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('is-active');
                    modal.style.display = 'none';
                }
            });
        }

        // Live Header Search Integration
        const searchInput = document.getElementById('global_header_search');
        if (searchInput) {
            @if(request('search'))
                searchInput.value = {!! json_encode(request('search')) !!};
            @endif

            searchInput.addEventListener('input', function() {
                const term = this.value.toLowerCase().trim();
                const cards = document.querySelectorAll('.products-grid .product-card');
                let visibleCount = 0;
                cards.forEach(card => {
                    const text = card.textContent.toLowerCase();
                    if (!term || text.includes(term)) {
                        card.style.display = '';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });
            });

            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const url = new URL(window.location.href);
                    if (this.value.trim()) {
                        url.searchParams.set('search', this.value.trim());
                    } else {
                        url.searchParams.delete('search');
                    }
                    url.searchParams.delete('page');
                    window.location.href = url.toString();
                }
            });
        }
    });
</script>
@endpush
@endsection
