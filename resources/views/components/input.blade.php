@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'error' => null,
    'help' => null,
])

<div class="form-group">
    @if($label)
        <label for="{{ $attributes->get('id', $name) }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="required-star">*</span>
            @endif
        </label>
    @endif
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $attributes->get('id', $name) }}"
        value="{{ old($name, $value) }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required) required @endif
        {{ $attributes->merge(['class' => 'form-control-input' . ($errors->has($name) || $error ? ' is-invalid' : '')]) }}
    >
    @if($error || $errors->has($name))
        <span class="form-error">{{ $error ?? $errors->first($name) }}</span>
    @elseif($help)
        <span class="form-help">{{ $help }}</span>
    @endif
</div>
