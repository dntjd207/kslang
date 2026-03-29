<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-20 hidden lg:hidden"></div>

<aside id="sidebar"
       class="fixed lg:static inset-y-0 left-0 z-30 w-64 bg-gray-900 text-white
              transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out">
    <div class="flex flex-col h-full">
        <div class="flex items-center justify-center h-16 border-b border-gray-800">
            <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-indigo-400">
                kslang Admin
            </a>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
            @php
                $menus = [
                    ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0h4'],
                    ['route' => 'admin.categories.index', 'pattern' => 'admin.categories.*', 'label' => 'Categories', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z'],
                    ['route' => 'admin.slangs.index', 'pattern' => 'admin.slangs.*', 'label' => 'Slangs', 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
                    ['route' => 'admin.api-playground.index', 'pattern' => 'admin.api-playground.*', 'label' => 'API Playground', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V7a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2zM9 3h6'],
                    ['route' => 'admin.pages.edit', 'params' => ['slug' => 'terms'], 'pattern' => 'admin.pages.*', 'label' => 'Pages', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ['route' => 'admin.app-settings.edit', 'pattern' => 'admin.app-settings.*', 'label' => 'App Settings', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                ];
            @endphp

            @foreach ($menus as $menu)
                @php
                    $isActive = request()->routeIs($menu['pattern']);
                    $href = route($menu['route'], $menu['params'] ?? []);
                @endphp
                <a href="{{ $href }}"
                   class="flex items-center px-4 py-2.5 rounded-lg text-sm font-medium transition
                          {{ $isActive
                              ? 'bg-indigo-600 text-white'
                              : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $menu['icon'] }}"/>
                    </svg>
                    {{ $menu['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</aside>
