@props(['type' => 'info', 'dismissible' => true, 'icon' => null])

@php
    $alertClasses = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info',
        'primary' => 'alert-primary',
        'secondary' => 'alert-secondary',
        'light' => 'alert-light',
        'dark' => 'alert-dark'
    ];
    
    $iconClasses = [
        'success' => 'fas fa-check-circle',
        'error' => 'fas fa-exclamation-circle',
        'warning' => 'fas fa-exclamation-triangle',
        'info' => 'fas fa-info-circle',
        'primary' => 'fas fa-info-circle',
        'secondary' => 'fas fa-info-circle',
        'light' => 'fas fa-info-circle',
        'dark' => 'fas fa-info-circle'
    ];
    
    $alertClass = $alertClasses[$type] ?? 'alert-info';
    $iconClass = $icon ?? $iconClasses[$type] ?? 'fas fa-info-circle';
@endphp

<div {{ $attributes->merge(['class' => 'alert ' . $alertClass . ($dismissible ? ' alert-dismissible fade show' : '')]) }} role="alert">
    @if($iconClass)
        <i class="{{ $iconClass }} me-2"></i>
    @endif
    
    {{ $slot }}
    
    @if($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>