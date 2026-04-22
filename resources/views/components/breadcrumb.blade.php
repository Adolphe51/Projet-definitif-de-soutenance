<!-- Ressource: resources/views/components/breadcrumb.blade.php -->
<nav class="breadcrumb" aria-label="Chemin de navigation">
    @foreach($items as $index => $item)
        @if($index < count($items) - 1)
            <li>
                <a href="{{ $item['url'] ?? '#' }}">{{ $item['label'] }}</a>
            </li>
        @else
            <li>
                <span>{{ $item['label'] }}</span>
            </li>
        @endif
    @endforeach
</nav>
