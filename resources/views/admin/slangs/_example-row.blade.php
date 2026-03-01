<div class="example-row flex flex-col sm:flex-row items-start gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200"
     data-index="{{ $index }}">

    <span class="drag-handle-example cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600 shrink-0 sm:mt-7">
        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
            <path d="M7 2a2 2 0 10.001 4.001A2 2 0 007 2zm0 6a2 2 0 10.001 4.001A2 2 0 007 8zm0 6a2 2 0 10.001 4.001A2 2 0 007 14zm6-8a2 2 0 10-.001-4.001A2 2 0 0013 6zm0 2a2 2 0 10.001 4.001A2 2 0 0013 8zm0 6a2 2 0 10.001 4.001A2 2 0 0013 14z"/>
        </svg>
    </span>

    @if (!empty($example['id']))
        <input type="hidden" name="examples[{{ $index }}][id]" value="{{ $example['id'] }}">
    @endif

    <div class="flex-1 w-full">
        <label class="block text-sm font-medium text-gray-700 mb-1">한국어 예문</label>
        <input type="text"
               name="examples[{{ $index }}][korean_example]"
               value="{{ old("examples.{$index}.korean_example", $example['korean_example'] ?? '') }}"
               placeholder="예: 씨발, 또 늦었어!"
               class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm @error("examples.{$index}.korean_example") border-red-500 @else border-gray-300 @enderror"
        />
        @error("examples.{$index}.korean_example")
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex-1 w-full">
        <label class="block text-sm font-medium text-gray-700 mb-1">영어 번역</label>
        <input type="text"
               name="examples[{{ $index }}][english_example]"
               value="{{ old("examples.{$index}.english_example", $example['english_example'] ?? '') }}"
               placeholder="예: F**k, I'm late again!"
               class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm @error("examples.{$index}.english_example") border-red-500 @else border-gray-300 @enderror"
        />
        @error("examples.{$index}.english_example")
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    <button type="button" class="remove-example shrink-0 sm:mt-7 p-1 text-red-400 hover:text-red-600 transition" title="예문 삭제">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
