{{-- Alert Komponenti --}}
@props(['type' => 'info', 'dismissible' => true, 'title' => null])

@php
$typeClasses = [
    'success' => 'alert-success',
    'error' => 'alert-danger',
    'warning' => 'alert-warning',
    'info' => 'alert-info',
    'danger' => 'alert-danger',
];

$icons = [
    'success' => 'check-circle',
    'error' => 'exclamation-triangle',
    'warning' => 'exclamation-triangle',
    'info' => 'info-circle',
    'danger' => 'exclamation-triangle',
];

$alertClass = $typeClasses[$type] ?? 'alert-info';
$icon = $icons[$type] ?? 'info-circle';
@endphp

<div {{ $attributes->merge(['class' => "alert {$alertClass}" . ($dismissible ? ' alert-dismissible fade show' : '')]) }} role="alert">
    <i class="bi bi-{{ $icon }}"></i>

    @if($title)
    <strong>{{ $title }}</strong>
    @endif

    {{ $slot }}

    @if($dismissible)
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>
