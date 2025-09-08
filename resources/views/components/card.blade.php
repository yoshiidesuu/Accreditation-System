@props([
    'title' => null,
    'subtitle' => null,
    'headerClass' => '',
    'bodyClass' => '',
    'footerClass' => '',
    'shadow' => true,
    'border' => false
])

@php
    $cardClasses = 'card';
    if ($shadow) $cardClasses .= ' shadow';
    if (!$border) $cardClasses .= ' border-0';
@endphp

<div {{ $attributes->merge(['class' => $cardClasses]) }}>
    @if($title || isset($header))
        <div class="card-header {{ $headerClass }}">
            @isset($header)
                {{ $header }}
            @else
                @if($title)
                    <h5 class="card-title mb-0">{{ $title }}</h5>
                @endif
                @if($subtitle)
                    <p class="card-subtitle text-muted mb-0">{{ $subtitle }}</p>
                @endif
            @endisset
        </div>
    @endif
    
    @if(isset($body) || $slot->isNotEmpty())
        <div class="card-body {{ $bodyClass }}">
            @isset($body)
                {{ $body }}
            @else
                {{ $slot }}
            @endisset
        </div>
    @endif
    
    @isset($footer)
        <div class="card-footer {{ $footerClass }}">
            {{ $footer }}
        </div>
    @endisset
</div>