@props(['title' => ''])

<header class="bg-white shadow-sm border-b border-gray-200 h-16 flex items-center justify-between px-6">
    <div class="flex items-center">
        <button id="sidebar-toggle" class="lg:hidden mr-4 p-2 rounded-md text-gray-600 hover:bg-gray-100">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <h1 class="text-lg font-semibold text-gray-800">{{ $title }}</h1>
    </div>

    <div class="flex items-center space-x-4">
        <span class="text-sm text-gray-600">{{ Auth::user()->name }}</span>

        <form method="POST" action="{{ route('admin.logout') }}" class="inline">
            @csrf
            <button type="submit"
                    class="text-sm text-red-600 hover:text-red-800 font-medium transition">
                로그아웃
            </button>
        </form>
    </div>
</header>
