@extends('layouts.app')

@section('title', 'PO #' . $purchaseOrder->po_number . ' - Bakery Management System')

@section('content')
<!-- Header Bar -->
<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <h2 style="font-size: 1.45rem; font-weight: 800; margin: 0; color: var(--text-primary, #0f172a); font-family: monospace;">
                #{{ $purchaseOrder->po_number }}
            </h2>
            @if($purchaseOrder->status === 'draft')
                <span class="status-pill pending" style="background: rgba(148, 163, 184, 0.15); color: #475569; font-weight: 700; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.8rem;">Draft</span>
            @elseif($purchaseOrder->status === 'ordered')
                <span class="status-pill inprogress" style="background: rgba(245, 158, 11, 0.15); color: #b45309; font-weight: 700; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.8rem;">Ordered (In Transit)</span>
            @elseif($purchaseOrder->status === 'received')
                <span class="status-pill completed" style="background: rgba(16, 185, 129, 0.15); color: #047857; font-weight: 700; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.8rem;">Received & Stocked In</span>
            @else
                <span class="status-pill danger" style="background: rgba(239, 68, 68, 0.15); color: #b91c1c; font-weight: 700; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.8rem;">Cancelled</span>
            @endif
        </div>
        <p style="font-size: 0.85rem; color: var(--text-muted, #64748b); margin-top: 0.35rem;">
            Created on {{ $purchaseOrder->created_at->format('M d, Y H:i') }} by {{ $purchaseOrder->user->name ?? 'System' }}
        </p>
    </div>

    <!-- Action Buttons -->
    <div style="display: flex; gap: 0.65rem; align-items: center; flex-wrap: wrap;">
        <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary" style="padding: 0.6rem 1.1rem; border-radius: 9999px; border: 1px solid var(--border-color, #e2e8f0); text-decoration: none; color: var(--text-primary, #1e293b); background: var(--bg-card, #ffffff); font-weight: 600;">
            &larr; Back to Orders
        </a>

        @if($purchaseOrder->isDraft())
            <a href="{{ route('admin.purchase-orders.edit', $purchaseOrder->id) }}" class="btn btn-secondary" style="padding: 0.6rem 1.1rem; border-radius: 9999px; border: 1px solid #d4d4d8; text-decoration: none; color: #2563eb; font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                <span>Edit Draft</span>
            </a>

            <form action="{{ route('admin.purchase-orders.order', $purchaseOrder->id) }}" method="POST" onsubmit="return confirm('Place order with vendor? Status will become Ordered.');" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.6rem 1.25rem; background: #b45309; color: #fff; border-radius: 9999px; border: none; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(180, 83, 9, 0.25);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    <span>Place Order (Mark as Ordered)</span>
                </button>
            </form>

            <form action="{{ route('admin.purchase-orders.cancel', $purchaseOrder->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this purchase order?');" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-secondary" style="padding: 0.6rem 1rem; border-radius: 9999px; border: 1px solid #ef4444; color: #ef4444; font-weight: 600; background: #fff; cursor: pointer;">
                    Cancel Order
                </button>
            </form>
        @elseif($purchaseOrder->isOrdered())
            <form action="{{ route('admin.purchase-orders.receive', $purchaseOrder->id) }}" method="POST" onsubmit="return confirm('Receive Purchase Order #{{ $purchaseOrder->po_number }}?\n\nThis will:\n- Add items to inventory stock\n- Update ingredient costs\n- Mark this order as Received.');" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-success" id="btn_receive_po" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.35rem; font-weight: 700; cursor: pointer;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Receive Order & Replenish Stock</span>
                </button>
            </form>

            <form action="{{ route('admin.purchase-orders.cancel', $purchaseOrder->id) }}" method="POST" onsubmit="return confirm('Cancel this purchase order?');" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-outline-danger" style="padding: 0.6rem 1rem; font-weight: 600; cursor: pointer;">
                    Cancel Order
                </button>
            </form>
        @endif
    </div>
</div>

@if($purchaseOrder->isReceived())
    <div class="bakery-card" style="padding: 1rem 1.25rem; border-radius: 1rem; margin-bottom: 1.5rem; background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25); display: flex; align-items: center; gap: 0.75rem;">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <div>
            <div style="font-weight: 800; color: #065f46; font-size: 0.95rem;">Order Successfully Received & Stock Replenished</div>
            <div style="font-size: 0.8rem; color: #047857;">Received on {{ $purchaseOrder->received_date ? $purchaseOrder->received_date->format('M d, Y H:i') : '—' }}. Stock quantities and cost prices have been added to inventory.</div>
        </div>
    </div>
@elseif($purchaseOrder->isCancelled())
    <div class="bakery-card" style="padding: 1rem 1.25rem; border-radius: 1rem; margin-bottom: 1.5rem; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); display: flex; align-items: center; gap: 0.75rem;">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        <div>
            <div style="font-weight: 800; color: #991b1b; font-size: 0.95rem;">Purchase Order Cancelled</div>
            <div style="font-size: 0.8rem; color: #b91c1c;">This purchase order was cancelled. No stock replenishment occurred.</div>
        </div>
    </div>
@endif

<!-- Supplier & Order Metadata Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
    <!-- Supplier Details -->
    <div class="bakery-card" style="padding: 1.25rem 1.5rem; border-radius: 1.25rem; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
            <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted, #94a3b8);">Vendor Information</span>
            @if($purchaseOrder->supplier)
                <a href="{{ route('admin.suppliers.show', $purchaseOrder->supplier->id) }}" style="font-size: 0.8rem; color: #4d2c20; font-weight: 700; text-decoration: none;">View Profile &rarr;</a>
            @endif
        </div>
        @if($purchaseOrder->supplier)
            <div style="font-size: 1.1rem; font-weight: 800; color: #4d2c20;">{{ $purchaseOrder->supplier->name }}</div>
            <div style="font-size: 0.85rem; color: #475569; margin-top: 0.35rem;">
                👤 Contact: {{ $purchaseOrder->supplier->contact_person ?? 'Account Rep' }}
            </div>
            <div style="font-size: 0.85rem; color: #475569; margin-top: 0.2rem;">
                📞 Phone: {{ $purchaseOrder->supplier->phone ?? '—' }} | ✉️ {{ $purchaseOrder->supplier->email ?? '—' }}
            </div>
            <div style="font-size: 0.8rem; color: var(--text-muted, #64748b); margin-top: 0.35rem;">
                📍 {{ $purchaseOrder->supplier->address ?? 'No address registered' }}
            </div>
        @else
            <div style="color: #a1a1aa;">No vendor linked</div>
        @endif
    </div>

    <!-- Timeline & Schedule -->
    <div class="bakery-card" style="padding: 1.25rem 1.5rem; border-radius: 1.25rem; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0);">
        <div style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted, #94a3b8); margin-bottom: 0.75rem;">
            Order Dates & Schedule
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted, #64748b);">Order Date</div>
                <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-primary, #1e293b); margin-top: 0.15rem;">
                    {{ $purchaseOrder->order_date ? $purchaseOrder->order_date->format('M d, Y') : '—' }}
                </div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted, #64748b);">Expected Delivery</div>
                <div style="font-weight: 700; font-size: 0.95rem; color: #b45309; margin-top: 0.15rem;">
                    {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('M d, Y') : 'Flexible / Unspecified' }}
                </div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted, #64748b);">Received Date</div>
                <div style="font-weight: 700; font-size: 0.95rem; color: #059669; margin-top: 0.15rem;">
                    {{ $purchaseOrder->received_date ? $purchaseOrder->received_date->format('M d, Y H:i') : 'Pending' }}
                </div>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted, #64748b);">Created By</div>
                <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-primary, #1e293b); margin-top: 0.15rem;">
                    {{ $purchaseOrder->user->name ?? 'Admin Staff' }}
                </div>
            </div>
        </div>
    </div>
</div>

@if($purchaseOrder->notes)
    <div class="bakery-card" style="padding: 1rem 1.25rem; border-radius: 1rem; margin-bottom: 1.5rem; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0);">
        <div style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted, #94a3b8);">Order Notes & Special Instructions</div>
        <div style="font-size: 0.9rem; color: var(--text-primary, #334155); margin-top: 0.35rem; line-height: 1.5;">
            {{ $purchaseOrder->notes }}
        </div>
    </div>
@endif

<!-- Line Items Breakdown Table -->
<div class="bakery-card" style="border-radius: 1.25rem; overflow: hidden; background: var(--card-bg, #ffffff); border: 1px solid var(--border-color, #e2e8f0); margin-bottom: 1.5rem;">
    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color, #e2e8f0); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #4d2c20;">
            Ordered Ingredients ({{ $purchaseOrder->items->count() }})
        </h3>
        <span style="font-size: 0.85rem; color: var(--text-muted, #64748b);">Server-calculated totals</span>
    </div>

    <div class="table-responsive-wrapper">
        <table class="recent-orders-table" style="width: 100%;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ingredient</th>
                    <th>Quantity Ordered</th>
                    <th>Unit</th>
                    <th>Unit Purchase Price</th>
                    <th style="text-align: right;">Line Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrder->items as $index => $item)
                    <tr>
                        <td style="color: var(--text-muted, #94a3b8); font-size: 0.85rem;">{{ $index + 1 }}</td>
                        <td>
                            <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-primary, #1e293b);">
                                {{ $item->ingredient->name ?? 'Deleted Ingredient' }}
                            </div>
                            @if($item->ingredient)
                                <div style="font-size: 0.75rem; color: var(--text-muted, #64748b);">
                                    Current Inventory: {{ number_format($item->ingredient->quantity, 2) }} {{ $item->ingredient->unit }}
                                </div>
                            @endif
                        </td>
                        <td style="font-weight: 800; font-size: 1rem; color: #0f172a;">
                            {{ number_format($item->quantity, 2) }}
                        </td>
                        <td>
                            <span class="unit-badge" style="background: #f1f5f9; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 700; font-size: 0.75rem; color: #334155;">
                                {{ strtoupper($item->unit) }}
                            </span>
                        </td>
                        <td style="font-weight: 700; color: #475569;">
                            ${{ number_format($item->purchase_price, 2) }}
                        </td>
                        <td style="text-align: right; font-weight: 800; font-size: 1rem; color: #0f172a;">
                            ${{ number_format($item->subtotal, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; border-top: 2px solid var(--border-color, #e2e8f0);">
                    <td colspan="5" style="text-align: right; font-weight: 800; font-size: 1.1rem; color: var(--text-primary, #0f172a); padding: 1.25rem 1rem;">
                        Grand Total:
                    </td>
                    <td style="text-align: right; font-weight: 800; font-size: 1.4rem; color: #4d2c20; padding: 1.25rem 1rem;">
                        ${{ number_format($purchaseOrder->total_cost, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
