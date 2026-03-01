@extends('layouts.admin')

@section('title', '욕/슬랭 관리 - kslang Admin')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-800">욕/슬랭 관리</h2>
        <a href="{{ route('admin.slangs.create') }}">
            <x-common.button>
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                새 욕/슬랭 추가
            </x-common.button>
        </a>
    </div>

    @if ($slangs->total() > 0 || request()->hasAny(['search', 'level', 'category_id']))
        {{-- 검색 + 카테고리 필터 --}}
        <div class="flex flex-col sm:flex-row gap-3 mb-4">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="search-input"
                       value="{{ request('search') }}"
                       placeholder="한국어, 발음, 설명으로 검색..."
                       class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @if (request('search'))
                    <button type="button" id="search-clear"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif
            </div>

            <select id="category-filter"
                    class="px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:w-48">
                <option value="">전체 카테고리</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- 레벨 필터 탭 --}}
        <div class="flex flex-wrap gap-2 mb-4">
            @php
                $currentLevel = request('level');
                $levelTabs = [
                    '' => '전체',
                    1 => '1단계 순한맛',
                    2 => '2단계 중간맛',
                    3 => '3단계 매운맛',
                    4 => '4단계 극한맛',
                ];
            @endphp
            @foreach ($levelTabs as $value => $label)
                @php
                    $isActive = ($currentLevel == $value) || ($value === '' && !$currentLevel);
                    $url = request()->fullUrlWithQuery(['level' => $value ?: null, 'page' => null]);
                @endphp
                <a href="{{ $url }}"
                   class="px-3 py-1.5 text-sm font-medium rounded-lg transition {{ $isActive ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if ($slangs->isEmpty())
            {{-- 검색 결과 없음 --}}
            <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <p class="text-gray-500 mb-1">검색 결과가 없습니다.</p>
                <p class="text-sm text-gray-400">다른 검색어를 입력하거나 필터를 변경해보세요.</p>
            </div>
        @else
            {{-- 테이블 --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="w-10 px-3 py-3"></th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">한국어</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">영어 발음</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">레벨</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">카테고리</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">빈도</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">상태</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">관리</th>
                            </tr>
                        </thead>
                        <tbody id="slang-list" class="bg-white divide-y divide-gray-100">
                            @foreach ($slangs as $slang)
                                <tr class="slang-item hover:bg-gray-50/50 transition-colors duration-100 {{ !$slang->is_active ? 'bg-gray-50 opacity-60' : '' }}"
                                    data-id="{{ $slang->id }}">
                                    <td class="px-3 py-3">
                                        <div class="drag-handle cursor-grab active:cursor-grabbing text-gray-300 hover:text-gray-500 transition-colors">
                                            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M7 2a2 2 0 10.001 4.001A2 2 0 007 2zm0 6a2 2 0 10.001 4.001A2 2 0 007 8zm0 6a2 2 0 10.001 4.001A2 2 0 007 14zm6-8a2 2 0 10-.001-4.001A2 2 0 0013 6zm0 2a2 2 0 10.001 4.001A2 2 0 0013 8zm0 6a2 2 0 10.001 4.001A2 2 0 0013 14z"/>
                                            </svg>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-800">{{ $slang->korean }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $slang->pronunciation }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $levelColors = [
                                                1 => 'bg-green-100 text-green-800',
                                                2 => 'bg-yellow-100 text-yellow-800',
                                                3 => 'bg-orange-100 text-orange-800',
                                                4 => 'bg-red-100 text-red-800',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center h-5 px-2 text-[11px] font-medium rounded-full {{ $levelColors[$slang->level] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $slang->level }}단계
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($slang->categories as $cat)
                                                <span class="inline-flex items-center h-5 px-2 text-[11px] font-medium rounded-full bg-indigo-50 text-indigo-600">
                                                    {{ $cat->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $slang->usage_frequency }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <button type="button"
                                                class="toggle-active relative w-10 h-[22px] rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $slang->is_active ? 'bg-indigo-600' : 'bg-gray-300' }}"
                                                data-id="{{ $slang->id }}"
                                                role="switch"
                                                aria-checked="{{ $slang->is_active ? 'true' : 'false' }}">
                                            <span class="block w-[18px] h-[18px] bg-white rounded-full shadow-sm transition-transform duration-200 absolute top-[2px] {{ $slang->is_active ? 'translate-x-[20px]' : 'translate-x-[2px]' }}"></span>
                                        </button>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.slangs.edit', $slang) }}">
                                                <x-common.button variant="secondary" size="sm">수정</x-common.button>
                                            </a>
                                            <x-common.button variant="danger" size="sm"
                                                             onclick="openDeleteModal({{ $slang->id }}, '{{ addslashes($slang->korean) }}')">
                                                삭제
                                            </x-common.button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $slangs->links('components.admin.pagination') }}
        @endif
    @else
        {{-- 데이터 0건 --}}
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            <p class="text-gray-500 mb-1">등록된 욕/슬랭이 없습니다.</p>
            <p class="text-sm text-gray-400 mb-6">아래 버튼을 눌러 첫 욕/슬랭을 추가하세요.</p>
            <a href="{{ route('admin.slangs.create') }}">
                <x-common.button>
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    새 욕/슬랭 추가
                </x-common.button>
            </a>
        </div>
    @endif

    {{-- 삭제 확인 모달 --}}
    <div id="delete-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeDeleteModal()"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="max-w-sm w-full bg-white rounded-xl shadow-xl transform transition-all">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">욕/슬랭 삭제</h3>
                    <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5">
                    <p class="text-sm text-gray-700">
                        '<span id="delete-slang-name" class="font-semibold"></span>'을(를) 삭제하시겠습니까?
                    </p>
                    <p class="text-xs text-gray-400 mt-2">연관된 사용 예문, 카테고리 연결, 음성 파일이 모두 삭제됩니다.</p>
                    <p class="text-xs text-gray-400">이 작업은 되돌릴 수 없습니다.</p>
                </div>

                <div class="px-6 py-3 bg-gray-50 rounded-b-xl flex justify-end space-x-3">
                    <x-common.button variant="secondary" size="sm" type="button" onclick="closeDeleteModal()">
                        취소
                    </x-common.button>
                    <x-common.button variant="danger" size="sm" type="button" id="confirm-delete">
                        삭제
                    </x-common.button>
                </div>
            </div>
        </div>
    </div>

    {{-- 토스트 컨테이너 --}}
    <div id="toast-container" class="fixed top-4 right-4 z-[60] space-y-2 pointer-events-none"></div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // === 토스트 알림 ===
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const colors = {
                success: { border: 'border-l-green-500', icon: 'text-green-500', iconPath: 'M5 13l4 4L19 7' },
                error: { border: 'border-l-red-500', icon: 'text-red-500', iconPath: 'M6 18L18 6M6 6l12 12' },
            };
            const color = colors[type] || colors.success;

            const toast = document.createElement('div');
            toast.className = `pointer-events-auto bg-white border border-gray-200 border-l-4 ${color.border} rounded-lg shadow-lg px-4 py-3 min-w-[280px] max-w-[420px] flex items-center gap-3 animate-slide-in`;
            toast.innerHTML = `
                <svg class="w-5 h-5 ${color.icon} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${color.iconPath}"/>
                </svg>
                <p class="text-sm text-gray-700 flex-1">${message}</p>
            `;

            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                toast.style.transition = 'all 0.25s ease-in';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // === 검색 debounce ===
        let searchTimer = null;
        const searchInput = document.getElementById('search-input');
        const searchClear = document.getElementById('search-clear');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimer);
                const query = this.value.trim();

                searchTimer = setTimeout(() => {
                    if (query.length === 0 || query.length >= 2) {
                        const url = new URL(window.location.href);
                        if (query) {
                            url.searchParams.set('search', query);
                        } else {
                            url.searchParams.delete('search');
                        }
                        url.searchParams.delete('page');
                        window.location.href = url.toString();
                    }
                }, 500);
            });
        }

        if (searchClear) {
            searchClear.addEventListener('click', function () {
                const url = new URL(window.location.href);
                url.searchParams.delete('search');
                url.searchParams.delete('page');
                window.location.href = url.toString();
            });
        }

        // === 카테고리 필터 ===
        const categoryFilter = document.getElementById('category-filter');
        if (categoryFilter) {
            categoryFilter.addEventListener('change', function () {
                const url = new URL(window.location.href);
                if (this.value) {
                    url.searchParams.set('category_id', this.value);
                } else {
                    url.searchParams.delete('category_id');
                }
                url.searchParams.delete('page');
                window.location.href = url.toString();
            });
        }

        // === 활성/비활성 토글 ===
        document.querySelectorAll('.toggle-active').forEach(toggle => {
            toggle.addEventListener('click', function () {
                const id = this.dataset.id;
                const btn = this;
                const row = btn.closest('.slang-item');
                const dot = btn.querySelector('span');

                fetch(`/admin/slangs/${id}/toggle`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.is_active) {
                            btn.classList.remove('bg-gray-300');
                            btn.classList.add('bg-indigo-600');
                            dot.classList.remove('translate-x-[2px]');
                            dot.classList.add('translate-x-[20px]');
                            row.classList.remove('bg-gray-50', 'opacity-60');
                            btn.setAttribute('aria-checked', 'true');
                        } else {
                            btn.classList.remove('bg-indigo-600');
                            btn.classList.add('bg-gray-300');
                            dot.classList.remove('translate-x-[20px]');
                            dot.classList.add('translate-x-[2px]');
                            row.classList.add('bg-gray-50', 'opacity-60');
                            btn.setAttribute('aria-checked', 'false');
                        }
                        showToast(data.message, 'success');
                    }
                })
                .catch(() => {
                    showToast('상태 변경에 실패했습니다.', 'error');
                });
            });
        });

        // === 삭제 모달 ===
        let deleteSlangId = null;

        window.openDeleteModal = function (id, korean) {
            deleteSlangId = id;
            document.getElementById('delete-slang-name').textContent = korean;
            openModal('delete-modal');
        };

        function closeDeleteModal() {
            closeModal('delete-modal');
            deleteSlangId = null;
        }

        document.getElementById('confirm-delete')?.addEventListener('click', function () {
            if (!deleteSlangId) return;
            const btn = this;
            btn.disabled = true;

            fetch(`/admin/slangs/${deleteSlangId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeDeleteModal();
                    showToast(data.message, 'success');
                    setTimeout(() => location.reload(), 500);
                }
            })
            .catch(() => {
                showToast('삭제에 실패했습니다.', 'error');
            })
            .finally(() => {
                btn.disabled = false;
            });
        });

        // === SortableJS 드래그 앤 드롭 ===
        document.addEventListener('DOMContentLoaded', function () {
            const list = document.getElementById('slang-list');
            if (!list) return;

            new Sortable(list, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'opacity-50',
                onEnd: function () {
                    const orders = [];
                    list.querySelectorAll('.slang-item').forEach((el, index) => {
                        orders.push({
                            id: parseInt(el.dataset.id),
                            sort_order: index,
                        });
                    });

                    fetch('/admin/slangs/reorder', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ orders })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                        }
                    })
                    .catch(() => {
                        showToast('정렬 저장에 실패했습니다.', 'error');
                    });
                }
            });
        });
    </script>

    <style>
        @keyframes slide-in {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .animate-slide-in {
            animation: slide-in 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
    </style>
@endpush
