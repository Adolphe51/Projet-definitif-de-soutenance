<!-- Ressource: resources/views/components/status-badge.blade.php -->
<span class="status-badge status-{{ $status ?? 'inactive' }}" title="{{ $title ?? '' }}">
    {{ $label ?? ucfirst($status ?? 'inactive') }}
</span>