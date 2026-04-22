<!-- Ressource: resources/views/components/progress-bar.blade.php -->
<div class="progress-bar">
    <div class="progress-bar-fill" style="width: {{ ($percentage ?? 0) }}%"></div>
</div>
@if($showLabel ?? true)
    <p class="text-muted text-center">{{ $label ?? '' }} {{ $percentage ?? 0 }}%</p>
@endif
