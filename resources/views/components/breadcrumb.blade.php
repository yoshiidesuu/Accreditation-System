@props(['items' => [], 'separator' => '/'])

@php
    // If items is a string, convert to array format
    if (is_string($items)) {
        $items = [['title' => $items, 'url' => null]];
    }
    
    // Ensure items is an array
    if (!is_array($items)) {
        $items = [];
    }
@endphp

@if(count($items) > 0)
<nav {{ $attributes->merge(['class' => 'breadcrumb-nav']) }} aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="{{ url('/') }}" class="text-decoration-none">
                <i class="fas fa-home"></i>
            </a>
        </li>
        
        @foreach($items as $index => $item)
            @php
                $isLast = $index === count($items) - 1;
                $title = is_array($item) ? ($item['title'] ?? $item['name'] ?? 'Unknown') : $item;
                $url = is_array($item) ? ($item['url'] ?? $item['href'] ?? null) : null;
            @endphp
            
            <li class="breadcrumb-item {{ $isLast ? 'active' : '' }}" {{ $isLast ? 'aria-current=page' : '' }}>
                @if($isLast || !$url)
                    {{ $title }}
                @else
                    <a href="{{ $url }}" class="text-decoration-none">{{ $title }}</a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@endif

<style>
.breadcrumb {
    background-color: transparent;
    padding: 0;
    margin-bottom: 1rem;
}

.breadcrumb-item {
    font-size: 0.9rem;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "{{ $separator }}";
    color: #6c757d;
}

.breadcrumb-item a {
    color: var(--maroon-primary, #800000);
}

.breadcrumb-item a:hover {
    color: var(--maroon-secondary, #a52a2a);
}

.breadcrumb-item.active {
    color: #6c757d;
}
</style>