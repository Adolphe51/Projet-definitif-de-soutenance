<!-- Ressource: resources/views/components/card.blade.php -->
<div class="card">
    @if($title ?? null)
        <div class="card-header">
            <h3 class="card-title">{{ $title }}</h3>
            @if($action ?? null)
                <div>{{ $action }}</div>
            @endif
        </div>
    @endif
    <div class="card-content">
        {{ $slot }}
    </div>
</div>
