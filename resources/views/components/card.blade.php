@props([
    'title' => null,
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if($title || isset($actions))
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color, #eee9e0);">
            <div class="card-title-group">
                @if($title)
                    <h3 class="card-title" style="font-size: 1.1rem; font-weight: 700; color: var(--text-title, #29150d); margin: 0;">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="card-subtitle" style="font-size: 0.8rem; color: var(--text-secondary, #6b7280); margin-top: 0.2rem;">{{ $subtitle }}</p>
                @endif
            </div>
            @if(isset($actions))
                <div class="card-actions" style="display: flex; align-items: center; gap: 0.5rem;">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif
    <div class="card-body" style="padding: 1.5rem;">
        {{ $slot }}
    </div>
    @if(isset($footer))
        <div class="card-footer" style="padding: 1rem 1.5rem; border-top: 1px solid var(--border-color, #eee9e0); background: rgba(0,0,0,0.015);">
            {{ $footer }}
        </div>
    @endif
</div>
