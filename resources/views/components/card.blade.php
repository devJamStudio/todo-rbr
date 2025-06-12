@props([
    'variant' => 'default',
    'hover' => true,
    'padding' => true
])

@php
$baseClasses = 'bg-white rounded-xl shadow-sm border border-gray-100';
$variants = [
    'default' => '',
    'primary' => 'border-blue-100 bg-blue-50',
    'success' => 'border-green-100 bg-green-50',
    'warning' => 'border-yellow-100 bg-yellow-50',
    'danger' => 'border-red-100 bg-red-50',
];
$hoverClasses = 'transition-all duration-200 hover:shadow-md hover:-translate-y-0.5';
$paddingClasses = 'p-6';
@endphp

<div {{ $attributes->merge([
    'class' => $baseClasses . ' ' . $variants[$variant] . ($hover ? ' ' . $hoverClasses : '') . ($padding ? ' ' . $paddingClasses : '')
]) }}>
    {{ $slot }}
</div>
