@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                {{ isset($word) ? '단어 수정' : '새 단어 추가' }}
            </h3>
        </div>
        
        <div class="px-4 py-5 sm:p-6">
            @if($errors->any())
                <div class="rounded-md bg-red-50 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm leading-5 font-medium text-red-800">
                                입력 오류가 발생했습니다
                            </h3>
                            <div class="mt-2 text-sm leading-5 text-red-700">
                                <ul class="list-disc pl-5">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ isset($word) ? route('admin.words.update', $word) : route('admin.words.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @if(isset($word))
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <!-- Korean Word -->
                    <div class="sm:col-span-3">
                        <label for="word_korean" class="block text-sm font-medium text-gray-700">한국어 단어 *</label>
                        <div class="mt-1">
                            <input type="text" name="word_korean" id="word_korean" value="{{ old('word_korean', $word->word_korean ?? '') }}" required
                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md border p-2">
                        </div>
                    </div>

                    <!-- Level -->
                    <div class="sm:col-span-3">
                        <label for="level" class="block text-sm font-medium text-gray-700">레벨 *</label>
                        <div class="mt-1">
                            <select id="level" name="level" required
                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md border p-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" {{ old('level', $word->level ?? 1) == $i ? 'selected' : '' }}>레벨 {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- English Pronunciation -->
                    <div class="sm:col-span-3">
                        <label for="word_english" class="block text-sm font-medium text-gray-700">영어 발음</label>
                        <div class="mt-1">
                            <input type="text" name="word_english" id="word_english" value="{{ old('word_english', $word->word_english ?? '') }}"
                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md border p-2">
                        </div>
                    </div>

                    <!-- Meaning -->
                    <div class="sm:col-span-3">
                        <label for="meaning" class="block text-sm font-medium text-gray-700">뜻</label>
                        <div class="mt-1">
                            <input type="text" name="meaning" id="meaning" value="{{ old('meaning', $word->meaning ?? '') }}"
                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md border p-2">
                        </div>
                    </div>

                    <!-- Etymology -->
                    <div class="sm:col-span-6">
                        <label for="etymology" class="block text-sm font-medium text-gray-700">어원</label>
                        <div class="mt-1">
                            <textarea id="etymology" name="etymology" rows="3"
                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md border p-2">{{ old('etymology', $word->etymology ?? '') }}</textarea>
                        </div>
                    </div>

                    <!-- Examples Section -->
                    <div class="sm:col-span-6">
                        <div class="flex justify-between items-center mb-3">
                            <label class="block text-sm font-medium text-gray-700">예문</label>
                            <button type="button" id="add-example-btn"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                예문 추가
                            </button>
                        </div>
                        
                        <div id="examples-container" class="space-y-3">
                            @if(isset($word) && $word->examples->count() > 0)
                                @foreach($word->examples as $index => $example)
                                    <div class="example-item bg-gray-50 p-4 rounded-lg border border-gray-200 relative" data-id="{{ $example->id }}">
                                        <div class="absolute left-2 top-1/2 -translate-y-1/2 cursor-move drag-handle">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-6">
                                            <input type="hidden" name="examples[{{ $index }}][id]" value="{{ $example->id }}">
                                            <div class="mb-2">
                                                <input type="text" name="examples[{{ $index }}][example_kr]" value="{{ $example->example_kr }}" 
                                                    placeholder="한국어 예문 *" required
                                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md border p-2">
                                            </div>
                                            <div>
                                                <input type="text" name="examples[{{ $index }}][example_en]" value="{{ $example->example_en }}" 
                                                    placeholder="영어 번역 (선택사항)"
                                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md border p-2">
                                            </div>
                                        </div>
                                        <button type="button" class="remove-example-btn absolute right-2 top-2 text-red-500 hover:text-red-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        
                        <p class="mt-2 text-xs text-gray-500">드래그하여 예문 순서를 변경할 수 있습니다.</p>
                    </div>

                    <!-- Tags -->
                    <div class="sm:col-span-6">
                        <label for="tags" class="block text-sm font-medium text-gray-700">태그</label>
                        <div class="mt-1">
                            <input type="text" name="tags" id="tags" value="{{ old('tags', $word->tags ?? '') }}" placeholder="쉼표로 구분된 태그"
                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md border p-2">
                        </div>
                    </div>

                    <!-- Audio File -->
                    <div class="sm:col-span-6">
                        <label class="block text-sm font-medium text-gray-700">오디오 파일</label>
                        
                        @if(isset($word) && $word->audio_filename)
                            <div class="mt-2 flex items-center">
                                <audio controls src="{{ asset('storage/audio/' . $word->audio_filename) }}" class="h-8"></audio>
                                <span class="ml-3 text-sm text-gray-500">현재 파일: {{ $word->audio_filename }}</span>
                            </div>
                        @endif

                        <div class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="audio" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>파일 업로드</span>
                                        <input id="audio" name="audio" type="file" accept="audio/*" class="sr-only">
                                    </label>
                                    <p class="pl-1">또는 드래그 앤 드롭</p>
                                </div>
                                <p class="text-xs text-gray-500">
                                    MP3, WAV, OGG 최대 5MB
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-5 border-t border-gray-200">
                    <div class="flex justify-end">
                        <a href="{{ route('admin.words.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            취소
                        </a>
                        <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ isset($word) ? '단어 수정' : '단어 생성' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
(function() {
    function createExampleItem(index) {
        var div = document.createElement('div');
        div.className = 'example-item bg-gray-50 p-4 rounded-lg border border-gray-200 relative';
        div.setAttribute('data-id', '');
        div.innerHTML = 
            '<div class="absolute left-2 top-1/2 -translate-y-1/2 cursor-move drag-handle">' +
                '<svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>' +
                '</svg>' +
            '</div>' +
            '<div class="ml-6">' +
                '<div class="mb-2">' +
                    '<input type="text" name="examples[' + index + '][example_kr]" value="" ' +
                        'placeholder="한국어 예문 *" required ' +
                        'class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md border p-2">' +
                '</div>' +
                '<div>' +
                    '<input type="text" name="examples[' + index + '][example_en]" value="" ' +
                        'placeholder="영어 번역 (선택사항)" ' +
                        'class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md border p-2">' +
                '</div>' +
            '</div>' +
            '<button type="button" class="remove-example-btn absolute right-2 top-2 text-red-500 hover:text-red-700">' +
                '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>' +
                '</svg>' +
            '</button>';
        return div;
    }

    function initExamples() {
        var container = document.getElementById('examples-container');
        var addBtn = document.getElementById('add-example-btn');
        
        if (!container || !addBtn) {
            console.error('Required elements not found: container=' + !!container + ', addBtn=' + !!addBtn);
            return;
        }
        
        // Initialize Sortable for drag and drop (if available)
        if (typeof Sortable !== 'undefined') {
            new Sortable(container, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'bg-blue-100',
                onEnd: function() {
                    reindexExamples();
                }
            });
        }
        
        // Add new example
        addBtn.addEventListener('click', function() {
            var index = container.querySelectorAll('.example-item').length;
            var newItem = createExampleItem(index);
            container.appendChild(newItem);
            // Focus on the new input
            var firstInput = newItem.querySelector('input[type="text"]');
            if (firstInput) firstInput.focus();
        });
        
        // Remove example
        container.addEventListener('click', function(e) {
            var removeBtn = e.target.closest('.remove-example-btn');
            if (removeBtn) {
                removeBtn.closest('.example-item').remove();
                reindexExamples();
            }
        });
        
        // Reindex all examples after add/remove/reorder
        function reindexExamples() {
            var items = container.querySelectorAll('.example-item');
            for (var i = 0; i < items.length; i++) {
                var inputs = items[i].querySelectorAll('input');
                for (var j = 0; j < inputs.length; j++) {
                    inputs[j].name = inputs[j].name.replace(/examples\[\d+\]/, 'examples[' + i + ']');
                }
            }
        }
    }
    
    // Run initialization
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initExamples);
    } else {
        initExamples();
    }
})();
</script>
@endpush
