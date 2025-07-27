{{-- resources/views/partials/home-navbar.blade.php --}}

<nav x-data="{ open: false }"
    class="fixed w-[calc(100%-2rem)] md:w-[calc(100%-4rem)] lg:w-[calc(100%-8rem)] z-50 transition-all duration-300 mx-4 md:mx-8 lg:mx-16"
    {{-- Tambahkan mx-auto --}}
    :class="{
        'top-4 bg-gray-300 dark:bg-white/90 shadow-lg rounded-full': window.scrollY >
            50,
        'top-4 bg-pink-300 rounded-full shadow-lg': window.scrollY <= 50
    }"
    {{-- top-2 dan rounded-full --}} @scroll.window="open = false">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex-shrink-0">
                <a href="{{ route('home') }}" class="lg:text-xl md:text-lg text-sm font-bold capitalize">
                    <span
                        :class="{
                            'text-white dark:text-gray-700': window.scrollY <=
                                50,
                            'text-green-400 dark:text-green-600': window.scrollY > 50
                        }"
                        class="px-1 rounded-full animate-[ping_1.9s_linear_infinite]">H</span>
                    <span
                        :class="{
                            'text-white dark:text-gray-700': window.scrollY <=
                                50,
                            'text-green-400 dark:text-green-600': window.scrollY > 50
                        }"
                        class="px-1 rounded-full animate-[ping_2s_linear_infinite]">A</span>
                    <span
                        :class="{
                            'text-white dark:text-gray-700': window.scrollY <=
                                50,
                            'text-green-400 dark:text-green-600': window.scrollY > 50
                        }"
                        class="px-1 rounded-full animate-[ping_2.1s_linear_infinite]">N</span>
                    <span
                        :class="{
                            'text-white dark:text-gray-700': window.scrollY <=
                                50,
                            'text-green-400 dark:text-green-600': window.scrollY > 50
                        }"
                        class="px-1 rounded-full animate-[ping_2.21s_linear_infinite]">A</span>
                    <span
                        :class="{
                            'text-white dark:text-gray-700': window.scrollY <=
                                50,
                            'text-green-400 dark:text-green-600': window.scrollY > 50
                        }"
                        class="px-1 rounded-full animate-[ping_2.3s_linear_infinite]">'</span>
                    <span
                        :class="{
                            'text-white dark:text-gray-700': window.scrollY <=
                                50,
                            'text-green-400 dark:text-green-600': window.scrollY > 50
                        }"
                        class="px-1 rounded-full animate-[ping_2.4s_linear_infinite]">S</span>
                </a>
            </div>

            <div class="hidden md:flex items-center space-x-8">
                <a href="{{ route('home') }}" class="text-white hover:text-pink-400 dark:hover:text-pink-600">Home</a>
                <a href="#fitur" class="text-white hover:text-pink-400 dark:hover:text-pink-600 ">Fitur</a>
                <a href="#profil-mc" class="text-white hover:text-pink-400 dark:hover:text-pink-600 ">Profile</a>
                <a href="#kontak" class="text-white hover:text-pink-400 dark:hover:text-pink-600 ">Contact</a>

                @guest
                    <a href="{{ route('login') }}"
                        class="bg-pink-500 hover:bg-pink-700 text-white font-semibold py-2 px-4 rounded-full transition duration-300">Login</a>
                    <a href="{{ route('register') }}"
                        class="bg-transparent border border-pink-500 text-white hover:bg-pink-500 hover:text-white font-semibold py-2 px-4 rounded-full transition duration-300 ml-2">Daftar</a>
                @endguest
                @auth
                    @php
                        $dashboardRoute = 'dashboard';
                        if (Auth::user()->isAdmin()) {
                            $dashboardRoute = 'admin.dashboard';
                        } elseif (Auth::user()->isEditor()) {
                            $dashboardRoute = 'editor.dashboard';
                        }
                    @endphp
                    <a href="{{ route($dashboardRoute) }}"
                        class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-full transition duration-300">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline-block ml-2">
                        @csrf
                        <button type="submit"
                            class="bg-transparent border border-gray-400 text-gray-400 hover:bg-gray-700 hover:text-white font-semibold py-2 px-4 rounded-full transition duration-300">Logout</button>
                    </form>
                @endauth
            </div>

            <div class="md:hidden flex items-center">
                <button @click="open = !open"
                    class="text-white hover:text-pink-400 dark:hover:text-pink-600 focus:outline-none">
                    <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16m-7 6h7" />
                        <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div x-show="open" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-4"
        class="md:hidden absolute left-0 w-full bg-[#111111] dark:bg-white bg-opacity-95 shadow-lg pt-2 pb-4 px-4 overflow-hidden rounded-b-xl"
        {{-- Tambahkan rounded-b-xl --}}
        :class="{ 'top-16': window.scrollY <= 50, 'top-[calc(theme(spacing.2)+theme(height.16))]': window.scrollY > 50 }"
        {{-- Penyesuaian top untuk mobile menu --}} @click.away="open = false" style="display: none;">
        <nav class="space-y-2 text-gray-400 dark:text-gray-700"> {{-- Kurangi space-y --}}
            <a href="{{ route('home') }}"
                class="block px-4 py-2 hover:bg-indigo-600 text-white dark:text-gray-700 dark:hover:text-white rounded">Home</a>
            <a href="#fitur"
                class="block px-4 py-2 hover:bg-indigo-600 text-white dark:text-gray-700 dark:hover:text-white rounded">Fitur</a>
            <a href="#profil-mc"
                class="block px-4 py-2 hover:bg-indigo-600 text-white dark:text-gray-700 dark:hover:text-white rounded">Profile</a>
            <a href="#kontak"
                class="block px-4 py-2 hover:bg-indigo-600 text-white dark:text-gray-700 dark:hover:text-white rounded">Contact</a>

            @guest
                <a href="{{ route('login') }}"
                    class="block px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-semibold rounded mt-4">Login</a>
                <a href="{{ route('register') }}"
                    class="block px-4 py-2 border border-green-500 text-green-500 hover:bg-green-500 hover:text-white font-semibold rounded mt-2">Daftar</a>
            @endguest
            @auth
                @php
                    $dashboardRoute = 'dashboard';
                    if (Auth::user()->isAdmin()) {
                        $dashboardRoute = 'admin.dashboard';
                    } elseif (Auth::user()->isEditor()) {
                        $dashboardRoute = 'editor.dashboard';
                    }
                @endphp
                <a href="{{ route($dashboardRoute) }}"
                    class="block px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-semibold rounded mt-4">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="block w-full text-left px-4 py-2 border border-gray-400 text-gray-400 hover:bg-gray-700 hover:text-white font-semibold rounded mt-2">Logout</button>
                </form>
            @endauth
        </nav>
    </div>
</nav>
