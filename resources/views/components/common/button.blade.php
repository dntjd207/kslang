@props([
    'variant' => 'primary',
    'type' => 'button',
    'size' => 'md',
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition focus:outline-none focus:ring-2 focus:ring-offset-2';

    $variantClasses = match ($variant) {
        'primary'   => 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500',
        'secondary' => 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-indigo-500',
        'danger'    => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'success'   => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
        default     => 'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500',
    };

    $sizeClasses = match ($size) {
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
        default => 'px-4 py-2 text-sm',
    };
@endphp

<button type="{{ $type }}"
        {{ $attributes->merge(['class' => "$baseClasses $variantClasses $sizeClasses"]) }}>
    {{ $slot }}
</button>
