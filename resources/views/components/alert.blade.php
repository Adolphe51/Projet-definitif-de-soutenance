<!-- Ressource: resources/views/components/alert.blade.php -->
<div class="alert alert-{{ $type ?? 'info' }}">
    <strong>{{ $title ?? (ucfirst($type ?? 'info') . ':') }}</strong>
    {{ $slot }}
</div>