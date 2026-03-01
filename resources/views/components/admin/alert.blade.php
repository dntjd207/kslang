@php
    $types = [
        'success' => ['bg' => 'bg-green-50', 'border' => 'border-green-400', 'text' => 'text-green-800', 'icon' => 'M5 13l4 4L19 7'],
        'error'   => ['bg' => 'bg-red-50', 'border' => 'border-red-400', 'text' => 'text-red-800', 'icon' => 'M6 18L18 6M6 6l12 12'],
        'warning' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-400', 'text' => 'text-yellow-800', 'icon' => 'M12 9v2m0 4h.01M12 2a10 10 0 100 20 10 10 0 000-20z'],
        'info'    => ['bg' => 'bg-blue-50', 'border' => 'border-blue-400', 'text' => 'text-blue-800', 'icon' => 'M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z'],
    ];
@endphp

@foreach ($types as $type => $style)
    @if (session($type))
        <div class="{{ $style['bg'] }} {{ $style['border'] }} {{ $style['text'] }} border-l-4 p-4 mb-4 rounded-r-lg flex items-center justify-between"
             role="alert">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $style['icon'] }}"/>
                </svg>
                <span>{{ session($type) }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="ml-4 text-lg font-bold opacity-50 hover:opacity-100">&times;</button>
        </div>
    @endif
@endforeach
