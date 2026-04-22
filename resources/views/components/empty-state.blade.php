<!-- Ressource: resources/views/components/empty-state.blade.php -->
<div class="empty-state">
    @if($icon ?? null)
        <div class="empty-state-icon">{{ $icon }}</div>
    @else
        <div class="empty-state-icon">📭</div>
    @endif
    <h3 class="empty-state-title">{{ $title ?? 'Aucune donnée' }}</h3>
    <p class="empty-state-text">{{ $slot }}</p>
    @if($action ?? null)
        <div>{{ $action }}</div>
    @endif
</div>