@props([
    'type' => 'success',
    'message' => null,
    'dismissible' => true,
])

@php
    $alertType = $type === 'danger' ? 'error' : $type;
    $alertClass = 'alert alert-' . $alertType;
@endphp

<div {{ $attributes->merge(['class' => $alertClass, 'role' => 'alert']) }}>
    <div class="alert-content" style="flex: 1;">
        {{ $message ?? $slot }}
    </div>
    @if($dismissible)
        <button type="button" class="alert-close" onclick="this.closest('.alert').remove()" aria-label="Close">&times;</button>
    @endif
</div>
