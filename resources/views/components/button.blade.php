@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'disabled' => false
])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2';
$variants = [
    'primary' => 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500',
    'secondary' => 'bg-gray-600 hover:bg-gray-700 text-white focus:ring-gray-500',
    'success' => 'bg-green-600 hover:bg-green-700 text-white focus:ring-green-500',
    'danger' => 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500',
    'warning' => 'bg-yellow-600 hover:bg-yellow-700 text-white focus:ring-yellow-500',
    'info' => 'bg-indigo-600 hover:bg-indigo-700 text-white focus:ring-indigo-500',
    'light' => 'bg-gray-100 hover:bg-gray-200 text-gray-800 focus:ring-gray-500',
    'dark' => 'bg-gray-800 hover:bg-gray-900 text-white focus:ring-gray-500',
];
$sizes = [
    'sm' => 'px-3 py-1.5 text-sm',
    'md' => 'px-4 py-2 text-base',
    'lg' => 'px-6 py-3 text-lg',
];
$disabledClasses = 'opacity-50 cursor-not-allowed';
@endphp

<button
    {{ $attributes->merge([
        'type' => $type,
        'class' => $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size] . ($disabled ? ' ' . $disabledClasses : '')
    ]) }}
    {{ $disabled ? 'disabled' : '' }}
>
    {{ $slot }}
</button>
