@extends('layouts.admin')

@section('title', '카테고리 관리 - kslang Admin')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-800">카테고리 관리</h2>
        <x-common.button onclick="openCategoryCreateModal()">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            카테고리 추가
        </x-common.button>
    </div>

    @if ($categories->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            <p class="text-gray-500 mb-1">등록된 카테고리가 없습니다.</p>
            <p class="text-sm text-gray-400 mb-6">아래 버튼을 눌러 첫 카테고리를 추가하세요.</p>
            <x-common.button onclick="openCategoryCreateModal()">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                카테고리 추가
            </x-common.button>
        </div>
    @else
        <div id="category-list" class="space-y-2">
            @foreach ($categories as $category)
                <div class="category-item bg-white rounded-xl border border-gray-200 px-4 py-3 flex items-center gap-4 hover:shadow-sm transition"
                     data-id="{{ $category->id }}">
                    <div class="drag-handle cursor-grab active:cursor-grabbing text-gray-300 hover:text-gray-500 transition-colors shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M7 2a2 2 0 10.001 4.001A2 2 0 007 2zm0 6a2 2 0 10.001 4.001A2 2 0 007 8zm0 6a2 2 0 10.001 4.001A2 2 0 007 14zm6-8a2 2 0 10-.001-4.001A2 2 0 0013 6zm0 2a2 2 0 10.001 4.001A2 2 0 0013 8zm0 6a2 2 0 10.001 4.001A2 2 0 0013 14z"/>
                        </svg>
                    </div>

                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold text-gray-800 truncate">{{ $category->name }}</h3>
                        @if ($category->description)
                            <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $category->description }}</p>
                        @endif
                    </div>

                    <span class="inline-flex items-center h-5 px-2 text-[11px] font-medium rounded-full bg-indigo-50 text-indigo-600 shrink-0">
                        {{ $category->slangs_count }}개
                    </span>

                    <div class="flex items-center gap-2 shrink-0">
                        <x-common.button variant="secondary" size="sm"
                                         onclick="openCategoryEditModal({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->description ?? '') }}')">
                            수정
                        </x-common.button>
                        <x-common.button variant="danger" size="sm"
                                         onclick="openCategoryDeleteModal({{ $category->id }}, '{{ addslashes($category->name) }}')">
                            삭제
                        </x-common.button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- 생성/수정 모달 --}}
    <div id="category-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeCategoryModal()"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="max-w-md w-full bg-white rounded-xl shadow-xl transform transition-all">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 id="modal-title" class="text-lg font-semibold text-gray-800">카테고리 추가</h3>
                    <button onclick="closeCategoryModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form id="category-form" onsubmit="submitCategory(event)">
                    <input type="hidden" id="category-id" value="">
                    <div class="px-6 py-4 space-y-4">
                        <div>
                            <label for="category-name" class="block text-sm font-medium text-gray-700 mb-1">
                                카테고리명 <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="category-name"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="카테고리명을 입력하세요">
                            <p id="error-name" class="error-message mt-1 text-xs text-red-500 hidden"></p>
                        </div>
                        <div>
                            <label for="category-description" class="block text-sm font-medium text-gray-700 mb-1">
                                설명
                            </label>
                            <textarea id="category-description" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-y"
                                      placeholder="카테고리에 대한 설명을 입력하세요"></textarea>
                            <p id="error-description" class="error-message mt-1 text-xs text-red-500 hidden"></p>
                        </div>
                    </div>

                    <div class="px-6 py-3 bg-gray-50 rounded-b-xl flex justify-end space-x-3">
                        <x-common.button variant="secondary" size="sm" type="button" onclick="closeCategoryModal()">
                            취소
                        </x-common.button>
                        <x-common.button size="sm" type="submit" id="category-submit-btn">
                            저장
                        </x-common.button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 삭제 확인 모달 --}}
    <div id="delete-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeDeleteModal()"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="max-w-sm w-full bg-white rounded-xl shadow-xl transform transition-all">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">카테고리 삭제</h3>
                    <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5">
                    <p class="text-sm text-gray-700">
                        '<span id="delete-category-name" class="font-semibold"></span>' 카테고리를 삭제하시겠습니까?
                    </p>
                    <p class="text-xs text-gray-400 mt-2">연결된 욕/슬랭과의 관계가 해제됩니다.</p>
                    <p class="text-xs text-gray-400">욕/슬랭 데이터는 삭제되지 않습니다.</p>
                </div>

                <input type="hidden" id="delete-category-id" value="">
                <div class="px-6 py-3 bg-gray-50 rounded-b-xl flex justify-end space-x-3">
                    <x-common.button variant="secondary" size="sm" type="button" onclick="closeDeleteModal()">
                        취소
                    </x-common.button>
                    <x-common.button variant="danger" size="sm" type="button" id="confirm-delete" onclick="submitDelete()">
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

        // === 모달 제어 ===
        function openCategoryCreateModal() {
            document.getElementById('modal-title').textContent = '카테고리 추가';
            document.getElementById('category-form').reset();
            document.getElementById('category-id').value = '';
            clearErrors();
            openModal('category-modal');
        }

        function openCategoryEditModal(id, name, description) {
            document.getElementById('modal-title').textContent = '카테고리 수정';
            document.getElementById('category-id').value = id;
            document.getElementById('category-name').value = name;
            document.getElementById('category-description').value = description || '';
            clearErrors();
            openModal('category-modal');
        }

        function openCategoryDeleteModal(id, name) {
            document.getElementById('delete-category-name').textContent = name;
            document.getElementById('delete-category-id').value = id;
            openModal('delete-modal');
        }

        function closeCategoryModal() {
            closeModal('category-modal');
            clearErrors();
        }

        function closeDeleteModal() {
            closeModal('delete-modal');
        }

        // === AJAX: 생성/수정 ===
        function submitCategory(e) {
            e.preventDefault();
            clearErrors();

            const id = document.getElementById('category-id').value;
            const name = document.getElementById('category-name').value;
            const description = document.getElementById('category-description').value;
            const isEdit = id !== '';

            const url = isEdit ? `/admin/categories/${id}` : '/admin/categories';
            const method = isEdit ? 'PUT' : 'POST';

            const submitBtn = document.getElementById('category-submit-btn');
            submitBtn.disabled = true;

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ name, description })
            })
            .then(response => {
                if (response.status === 422) {
                    return response.json().then(data => { throw data; });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    closeCategoryModal();
                    showToast(data.message, 'success');
                    setTimeout(() => location.reload(), 500);
                }
            })
            .catch(errorData => {
                if (errorData.errors) {
                    displayErrors(errorData.errors);
                } else {
                    showToast('요청 처리에 실패했습니다.', 'error');
                }
            })
            .finally(() => {
                submitBtn.disabled = false;
            });
        }

        // === AJAX: 삭제 ===
        function submitDelete() {
            const id = document.getElementById('delete-category-id').value;
            const deleteBtn = document.getElementById('confirm-delete');
            deleteBtn.disabled = true;

            fetch(`/admin/categories/${id}`, {
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
                deleteBtn.disabled = false;
            });
        }

        // === 유효성 에러 표시 ===
        function displayErrors(errors) {
            Object.keys(errors).forEach(field => {
                const errorEl = document.getElementById(`error-${field}`);
                if (errorEl) {
                    errorEl.textContent = errors[field][0];
                    errorEl.classList.remove('hidden');
                }
            });
        }

        function clearErrors() {
            document.querySelectorAll('.error-message').forEach(el => {
                el.textContent = '';
                el.classList.add('hidden');
            });
        }

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

        // === SortableJS 드래그 앤 드롭 ===
        document.addEventListener('DOMContentLoaded', function () {
            const list = document.getElementById('category-list');
            if (!list) return;

            new Sortable(list, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'opacity-50',
                onEnd: function () {
                    const orders = [];
                    list.querySelectorAll('.category-item').forEach((el, index) => {
                        orders.push({
                            id: parseInt(el.dataset.id),
                            sort_order: index,
                        });
                    });

                    fetch('/admin/categories/reorder', {
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
