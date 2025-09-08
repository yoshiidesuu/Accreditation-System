@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'outline' => false,
    'disabled' => false,
    'loading' => false,
    'icon' => null,
    'iconPosition' => 'left',
    'href' => null,
    'target' => null
])

@php
    $baseClasses = 'btn';
    
    // Variant classes
    if ($outline) {
        $baseClasses .= ' btn-outline-' . $variant;
    } else {
        $baseClasses .= ' btn-' . $variant;
    }
    
    // Size classes
    $sizeClasses = [
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg'
    ];
    
    if (isset($sizeClasses[$size])) {
        $baseClasses .= ' ' . $sizeClasses[$size];
    }
    
    // Disabled state
    if ($disabled || $loading) {
        $baseClasses .= ' disabled';
    }
    
    // Determine tag
    $tag = $href ? 'a' : 'button';
    
    // Additional attributes
    $additionalAttrs = [];
    if ($href) {
        $additionalAttrs['href'] = $href;
        if ($target) {
            $additionalAttrs['target'] = $target;
        }
    } else {
        $additionalAttrs['type'] = $type;
        if ($disabled || $loading) {
            $additionalAttrs['disabled'] = true;
        }
    }
@endphp

<{{ $tag }} 
    {{ $attributes->merge(array_merge(['class' => $baseClasses], $additionalAttrs)) }}
>
    @if($loading)
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
    @elseif($icon && $iconPosition === 'left')
        <i class="{{ $icon }} me-2"></i>
    @endif
    
    {{ $slot }}
    
    @if($icon && $iconPosition === 'right')
        <i class="{{ $icon }} ms-2"></i>
    @endif
</{{ $tag }}>