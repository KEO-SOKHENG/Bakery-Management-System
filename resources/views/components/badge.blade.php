@props([
    'status' => null,
    'variant' => null,
    'dot' => true,
])

@php
    $v = $variant;
    if (!$v && $status) {
        $cleanStatus = strtolower(str_replace([' ', '-'], '_', $status));
        $v = match($cleanStatus) {
            'completed', 'paid', 'active', 'in_stock', 'received', 'success' => 'success',
            'pending', 'preparing', 'low_stock', 'in_progress', 'warning' => 'warning',
            'cancelled', 'suspended', 'out_of_stock', 'expired', 'danger', 'failed' => 'danger',
            'ordered', 'scheduled', 'ready', 'ready_for_pickup', 'info' => 'info',
            default => 'neutral',
        };
    }
    $badgeClass = 'badge badge-' . ($v ?? 'neutral');
@endphp

<span {{ $attributes->merge(['class' => $badgeClass]) }}>
    @if($dot)
        <span class="badge-dot"></span>
    @endif
    {{ $slot->isEmpty() ? ucfirst(str_replace('_', ' ', $status ?? '')) : $slot }}
</span>
