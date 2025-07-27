{{-- resources/views/mc/show.blade.php --}}

@extends('layouts.app')

@section('title', 'Pesan MC: ' . $mc->user->name)

@section('navigation')
    {{-- Ini adalah navigasi untuk pengguna yang sudah login (user, admin, editor) --}}
    @include('layouts.navigation-auth')
@endsection

@section('content')
    <div class="min-h-screen flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-4xl">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 py-6 px-8 text-white">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-3xl font-bold">Pesan {{ $mc->user->name }}</h1>
                            <p class="text-indigo-100">MC Profesional untuk Acara Anda</p>
                        </div>
                        <div class="text-4xl">
                            <i class="fas fa-microphone"></i> {{-- Ikon Mikrofon --}}
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                            role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                            role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                            role="alert">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Alpine.js component serviceOptions() diimpor via JS, jadi x-data cukup memanggilnya --}}
                    <div x-data="serviceOptions()" class="space-y-4 mb-8">
                        <h3 class="text-2xl font-bold text-gray-800 border-b pb-2 mb-4">Pilih Jenis Layanan MC</h3>
                        <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
                            <template x-for="(service, index) in services" :key="index">
                                <label
                                    class="flex items-center space-x-3 mb-3 cursor-pointer p-3 rounded-md transition-colors duration-200"
                                    :class="{ 'bg-blue-100 border-blue-400': selectedServices.includes(service
                                        .value), 'bg-white border border-gray-300': !selectedServices.includes(service
                                            .value) }">
                                    <input type="checkbox" :value="service.value" x-model="selectedServices"
                                        @change="calculateTotal" class="form-checkbox text-blue-600 h-5 w-5 rounded">
                                    <span class="text-lg font-medium text-gray-800" x-text="service.label"></span>
                                    <span class="ml-auto font-bold text-gray-900"
                                        x-text="'Rp ' + service.price.toLocaleString('id-ID')"></span>
                                </label>
                            </template>

                            <div class="mt-6 pt-4 border-t border-gray-200">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xl font-semibold text-gray-800">Total Pilihan Layanan:</span>
                                    <span class="text-2xl font-bold text-indigo-700"
                                        x-text="'Rp ' + calculatedTotal.toLocaleString('id-ID')"></span>
                                    <input type="hidden" name="selected_service_types" :value="selectedServices.join(',')">
                                    <input type="hidden" name="calculated_service_price" :value="calculatedTotal">
                                </div>
                                <p class="text-sm text-gray-500">
                                    * Harga ini belum termasuk biaya layanan website (Rp 25.000). Harga final akan dihitung
                                    setelah Anda memilih tanggal dan durasi.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8"> {{-- Grid untuk Form dan Kalender --}}
                        <div>
                            <form action="{{ route('booking.store', $mc->id) }}" method="POST" class="space-y-6">
                                @csrf
                                <h2 class="text-2xl font-bold text-gray-800 border-b pb-2">Isi Detail Acara</h2>

                                <div class="space-y-4">
                                    <h3 class="text-lg font-semibold text-gray-700">Informasi Pemesan</h3>
                                    <div class="grid md:grid-cols-1 gap-6"> {{-- Gunakan 1 kolom untuk mobile --}}
                                        <div>
                                            <label for="fullName" class="block text-sm font-medium text-gray-700 mb-1">Nama
                                                Lengkap</label>
                                            <input type="text" id="fullName" name="fullName" required
                                                value="{{ Auth::user()->name ?? old('fullName') }}"
                                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label for="email"
                                                class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                            <input type="email" id="email" name="email" required
                                                value="{{ Auth::user()->email ?? old('email') }}"
                                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Nomor
                                                Telepon</label>
                                            <input type="tel" id="phone" name="phone" required
                                                value="{{ old('phone') }}"
                                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <h3 class="text-lg font-semibold text-gray-700">Detail Waktu & Lokasi Acara</h3>
                                    <div class="grid md:grid-cols-1 gap-6"> {{-- Gunakan 1 kolom untuk mobile --}}
                                        <div>
                                            <label for="event_date"
                                                class="block text-sm font-medium text-gray-700 mb-1">Tanggal Acara</label>
                                            <input type="date" id="event_date" name="event_date" required
                                                value="{{ old('event_date') }}"
                                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label for="event_start_time"
                                                class="block text-sm font-medium text-gray-700 mb-1">Waktu Mulai</label>
                                            <input type="time" id="event_start_time" name="event_start_time" required
                                                value="{{ old('event_start_time') }}"
                                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label for="event_end_time"
                                                class="block text-sm font-medium text-gray-700 mb-1">Waktu Selesai</label>
                                            <input type="time" id="event_end_time" name="event_end_time" required
                                                value="{{ old('event_end_time') }}"
                                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label for="event_type"
                                                class="block text-sm font-medium text-gray-700 mb-1">Jenis Acara</label>
                                            <input type="text" id="event_type" name="event_type" required
                                                value="{{ old('event_type') }}"
                                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div>
                                            <label for="location"
                                                class="block text-sm font-medium text-gray-700 mb-1">Lokasi Acara</label>
                                            <input type="text" id="location" name="location" required
                                                value="{{ old('location') }}"
                                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Catatan
                                        Tambahan (Opsional)</label>
                                    <textarea id="notes" name="notes" rows="3" value="{{ old('notes') }}"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Any special requirements or preferences..."></textarea>
                                </div>

                                <div class="space-y-4">
                                    <h3 class="text-lg font-semibold text-gray-700">Opsi Pembayaran</h3>
                                    <div class="grid md:grid-cols-2 gap-4">
                                        <label
                                            class="border rounded-lg p-4 hover:border-blue-500 hover:bg-blue-50 cursor-pointer">
                                            <input type="radio" name="payment_option" value="full"
                                                class="hidden peer">
                                            <div
                                                class="peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:ring-2 peer-checked:ring-blue-200 border rounded-lg p-3 text-center">
                                                <h4 class="font-medium text-gray-800 text-lg">Bayar Penuh</h4>
                                                <p class="text-sm text-gray-600 mt-1">Pembayaran seluruh biaya di muka.</p>
                                            </div>
                                        </label>
                                        <label
                                            class="border rounded-lg p-4 hover:border-blue-500 hover:bg-blue-50 cursor-pointer">
                                            <input type="radio" name="payment_option" value="dp"
                                                class="hidden peer" checked>
                                            <div
                                                class="peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:ring-2 peer-checked:ring-blue-200 border rounded-lg p-3 text-center">
                                                <h4 class="font-medium text-gray-800 text-lg">Bayar DP 50%</h4>
                                                <p class="text-sm text-gray-600 mt-1">Sisa pembayaran dilunasi kemudian.
                                                </p>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="flex items-center">
                                        <input id="agreeTerms" name="agreeTerms" type="checkbox" required
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="agreeTerms" class="ml-2 block text-sm text-gray-700">
                                            Saya menyetujui <a href="#" class="text-blue-600 hover:underline">Syarat
                                                & Ketentuan</a> dan
                                            <a href="#" class="text-blue-600 hover:underline">Kebijakan
                                                Pembatalan</a>
                                        </label>
                                    </div>

                                    <button type="submit"
                                        class="w-full py-3 px-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 rounded-lg text-white font-bold text-lg shadow-md hover:shadow-lg transition duration-300">
                                        <i class="fas fa-calendar-check mr-2"></i> Proses Pemesanan
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="pt-8 md:pt-0"> {{-- Tambah padding atas untuk mobile jika perlu --}}
                            <h3 class="text-2xl font-bold text-gray-800 border-b pb-2 mb-4">Ketersediaan MC</h3>
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <h4 class="text-base font-semibold text-gray-700 mb-3 text-center">Pilih Tanggal di Form
                                    untuk Melihat Detail</h4>
                                {{-- Tambahkan data-mc-id untuk JS --}}
                                <div id="calendar" class="w-full" data-mc-id="{{ $mc->id }}"></div>
                                <p class="text-sm text-gray-500 mt-2 text-center">Tanggal yang bisa dipesan hanya Sabtu dan
                                    Minggu. Tanggal yang terblokir berwarna abu-abu.</p>
                            </div>

                            <div class="mt-8 bg-white shadow-lg rounded-xl p-6 text-center border border-gray-200">
                                <h4 class="text-xl font-bold text-gray-800 mb-4">Profil Singkat {{ $mc->user->name }}</h4>
                                @if ($mc->profile_picture_url)
                                    <img src="{{ $mc->profile_picture_url }}" alt="Profile Picture"
                                        class="w-24 h-24 rounded-full mx-auto mb-4 object-cover shadow-md border-2 border-indigo-500">
                                @endif
                                <p class="text-gray-700 text-sm mb-2">{{ Str::limit($mc->bio, 100) }}</p>
                                <p class="text-md text-gray-600">
                                    Min. Durasi: <span class="font-semibold">{{ $mc->min_duration_hours }} jam</span>
                                </p>
                                <a href="#details" class="text-indigo-600 hover:underline text-sm mt-3 inline-block">Lihat
                                    Detail MC Lengkap</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-8 py-4 border-t">
                    <div class="flex flex-col md:flex-row justify-between items-center">
                        <div class="text-sm text-gray-600 mb-2 md:mb-0">
                            <i class="fas fa-phone-alt mr-1"></i> {{ $mc->contact_phone }}
                        </div>
                        <div class="text-sm text-gray-600 mb-2 md:mb-0">
                            <i class="fas fa-envelope mr-1"></i> {{ $mc->user->email }}
                        </div>
                        <div class="text-sm text-gray-600">
                            <i class="fas fa-map-marker-alt mr-1"></i> Depok, Jawa Barat, Indonesia
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('layouts.footer-custom')
@endsection
