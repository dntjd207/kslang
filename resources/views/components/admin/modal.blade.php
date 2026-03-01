@props([
    'id' => 'modal',
    'title' => '',
    'maxWidth' => 'md',
])

@php
    $widthClass = match ($maxWidth) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        default => 'max-w-md',
    };
@endphp

<div id="{{ $id }}"
     class="fixed inset-0 z-50 hidden"
     role="dialog"
     aria-modal="true">
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeModal('{{ $id }}')"></div>

    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="{{ $widthClass }} w-full bg-white rounded-xl shadow-xl transform transition-all">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
                <button onclick="closeModal('{{ $id }}')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-4">
                {{ $slot }}
            </div>

            @isset($footer)
                <div class="px-6 py-3 bg-gray-50 rounded-b-xl flex justify-end space-x-3">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
