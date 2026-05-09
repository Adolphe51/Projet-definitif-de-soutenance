<!-- Ressource: resources/views/components/card.blade.php -->
@props([
    'title' => null,
    'action' => null,
    'contentClass' => 'card-content',
])

<div {{ $attributes->class(['card']) }}>
    @if($title || $action)
        <div class="card-header">
            @if($title)
                <h3 class="card-title">{{ $title }}</h3>
            @endif
            @if($action)
                <div>{{ $action }}</div>
            @endif
        </div>
    @endif
    <div class="{{ $contentClass }}">
        {{ $slot }}
    </div>
</div>
