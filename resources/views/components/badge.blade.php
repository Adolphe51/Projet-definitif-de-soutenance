<!-- Ressource: resources/views/components/badge.blade.php -->
@props(['type' => null])

<span {{ $attributes->class(['badge', $type ? 'badge-' . $type : null]) }}>
    {{ $slot }}
</span>
