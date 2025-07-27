{{-- resources/views/layouts/navigation-auth.blade.php --}}

<nav x-data="{ open: false }" class="bg-indigo-700 text-white p-4 shadow-md">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <a href="{{ Auth::user()->isEditor() ? route('editor.dashboard') : (Auth::user()->isAdmin() ? route('admin.dashboard') : route('dashboard')) }}"
            class="text-white text-2xl font-bold">MC Booking</a>

        <div class="hidden md:flex items-center space-x-6">
            @if (Auth::user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-200">Dashboard MC</a>
                <a href="{{ route('admin.bookings.index') }}" class="hover:text-gray-200">Pemesanan Masuk</a>
                <a href="{{ route('admin.schedules.index') }}" class="hover:text-gray-200">Kelola Jadwal</a>
                {{-- Link baru --}}
            @elseif (Auth::user()->isUser())
                <a href="{{ route('dashboard') }}" class="hover:text-gray-200">Dashboard</a>
                <a href="{{ route('my.bookings.index') }}" class="hover:text-gray-200">Pemesanan Saya</a>
                <a href="{{ route('mc.show', ['id' => 1]) }}" class="hover:text-gray-200">Booking MC</a>
                {{-- Ganti 1 --}}
            @elseif (Auth::user()->isEditor())
                <a href="{{ route('editor.dashboard') }}" class="hover:text-gray-200">Editor Dashboard</a>
                {{-- Tambahkan link manajemen pengguna/konten --}}
            @endif

            <div x-data="{ dropdownOpen: false }" class="relative">
                <button @click="dropdownOpen = !dropdownOpen" class="flex items-center space-x-2 focus:outline-none">
                    <span>{{ Auth::user()->name }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div x-show="dropdownOpen" @click.outside="dropdownOpen = false"
                    class="absolute right-0 mt-2 py-2 w-48 bg-white rounded-md shadow-xl z-20">
                    <a href="{{ route('profile.edit') }}"
                        class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Profil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}" class="block px-4 py-2 text-gray-800 hover:bg-gray-100"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                            Logout
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <div class="md:hidden flex items-center">
            <button @click="open = !open" class="text-white focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                    </path>
                </svg>
            </button>
        </div>
    </div>

    <div x-show="open" @click.outside="open = false" class="md:hidden mt-4 space-y-2">
        @if (Auth::user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 hover:bg-indigo-600">Dashboard MC</a>
            <a href="{{ route('admin.bookings.index') }}" class="block px-4 py-2 hover:bg-indigo-600">Pemesanan
                Masuk</a>
            <a href="{{ route('admin.schedules.index') }}" class="block px-4 py-2 hover:bg-indigo-600">Kelola
                Jadwal</a>
        @elseif (Auth::user()->isUser())
            <a href="{{ route('dashboard') }}" class="block px-4 py-2 hover:bg-indigo-600">Dashboard</a>
            <a href="{{ route('my.bookings.index') }}" class="block px-4 py-2 hover:bg-indigo-600">Pemesanan Saya</a>
            <a href="{{ route('mc.show', ['id' => 1]) }}" class="block px-4 py-2 hover:bg-indigo-600">Booking MC</a>
        @elseif (Auth::user()->isEditor())
            <a href="{{ route('editor.dashboard') }}" class="block px-4 py-2 hover:bg-indigo-600">Editor Dashboard</a>
        @endif
        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-indigo-600">Profil</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <a href="{{ route('logout') }}" class="block px-4 py-2 hover:bg-indigo-600"
                onclick="event.preventDefault(); this.closest('form').submit();">
                Logout
            </a>
        </form>
    </div>
</nav>
