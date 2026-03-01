@extends('layouts.auth')

@section('title', '로그인 - kslang Admin')

@section('content')
<div class="w-full max-w-md px-4">
    <div class="bg-white rounded-lg shadow-md p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">kslang</h1>
            <p class="text-gray-500 mt-1">관리자 로그인</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-600 rounded-md p-3 mb-4">
                <ul class="list-none">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf

            <div class="mb-4">
                <label for="login_id" class="block text-sm font-medium text-gray-700 mb-1">아이디</label>
                <input
                    type="text"
                    id="login_id"
                    name="login_id"
                    value="{{ old('login_id') }}"
                    placeholder="아이디를 입력하세요"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('login_id') border-red-500 @enderror"
                    autofocus
                    required
                />
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">비밀번호</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="비밀번호를 입력하세요"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('password') border-red-500 @enderror"
                    required
                />
            </div>

            <div class="flex items-center mb-6">
                <input
                    type="checkbox"
                    id="remember"
                    name="remember"
                    {{ old('remember') ? 'checked' : '' }}
                    class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                />
                <label for="remember" class="ml-2 text-sm text-gray-600">로그인 상태 유지</label>
            </div>

            <button
                type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md transition duration-200"
            >
                로그인
            </button>
        </form>
    </div>
</div>
@endsection
