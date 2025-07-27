@extends('layouts.app')
@section('title', 'Beranda - Booking MC')

@section('navigation')
    @include('layouts.home.navbar')
@endsection

@section('content')
    <div
        class="relative min-h-screen bg-gradient-to-r from-red-100 to-purple-600 text-white flex items-center justify-center py-16 px-4">
        <div class="text-center pt-24 md:pt-0"> {{-- Sesuaikan pt-24 agar konten tidak tertutup navbar --}}
            <h1 class="text-5xl md:text-6xl font-extrabold leading-tight mb-4 animate__animated animate__fadeInDown">
                Master <span class="text-green-300">Of</span> Ceremony
            </h1>
            <p class="text-xl md:text-2xl opacity-90 mb-8 animate__animated animate__fadeInUp">

            </p>
            <a href="{{ route('login') }}"
                class="bg-white text-indigo-700 font-bold py-3 px-8 rounded-full text-lg hover:bg-gray-200 transition duration-300 shadow-lg inline-block animate__animated animate__zoomIn">
                Mulai Pesan Sekarang
            </a>
        </div>
    </div>

    <section id="pricing" class="flex items-center justify-center mt-10 pb-10">
        <div class="p-4 sm:px-10 flex flex-col justify-center items-center text-base w-full mx-auto">
            <div id="profil-mc" class="container mx-auto px-4 py-16">
                <h2 class="text-4xl font-bold text-center text-gray-800 mb-12">Profile</h2>
                <div class="p-4 max-w-6xl mx-auto text-center">
                    @if ($mc = \App\Models\Mc::with('user')->first())
                        <img src="{{ $mc->profile_picture_url ?: 'https://via.placeholder.com/150' }}"
                            alt="{{ $mc->user->name }}"
                            class="w-32 h-32 rounded-full mx-auto mb-4 object-cover border-4 border-indigo-600 shadow-md">
                        <h3 class="text-3xl font-bold text-gray-800 mb-2">Hana Nayla Kautsar</h3>
                        <!-- <h3 class="text-3xl font-bold text-gray-800 mb-2">{{ $mc->user->name }}</h3> -->
                        <p class="text-lg text-gray-600 mb-4">{{ $mc->bio }}</p>
                        <a href="{{ route('mc.show', ['id' => $mc->id]) }}"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-full inline-block transition duration-300">
                            My Portofolio
                        </a>
                    @else
                        <p class="text-gray-600">Profil MC belum tersedia.</p>
                    @endif
                </div>
            </div>
            <h3 class="text-4xl font-semibold text-center flex gap-2 justify-center mb-10 text-gray-800">Package</h3>
            <div class="isolate mx-auto grid max-w-md grid-cols-1 gap-8 lg:mx-0 lg:max-w-none lg:grid-cols-2">

                {{-- Card Standard --}}
                <div class="ring-1 ring-gray-200 rounded-3xl p-6 xl:p-8 bg-white">
                    <div class="flex items-center justify-between gap-x-4">
                        <h3 id="tier-standard" class="text-gray-900 text-2xl font-semibold leading-8">Standart</h3>
                    </div>
                    <p class="mt-4 text-base leading-6 text-gray-600">Cocok untuk acara individual</p>
                    <p class="mt-6 flex items-end gap-x-1 mx-auto">
                        <span class="text-3xl font-bold tracking-tight text-gray-900">
                            Rp{{ number_format($minStandardPrice, 0, ',', '.') }}</span>
                        <span class="line-through text-lg font-sans text-red-500/70">
                            Rp{{ number_format($strikeStandardPrice, 0, ',', '.') }}</span>
                    </p>
                    <a href="{{ route('mc.show', ['id' => $mc ? $mc->id : 1, 'package' => 'standard']) }}"
                        aria-describedby="tier-standard"
                        class="text-blue-600 ring-1 ring-inset ring-blue-200 hover:ring-blue-300 mt-6 block rounded-md py-2 px-3 text-center text-base font-medium leading-6 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                        target="_blank">Pilih Paket Standar</a>
                    <ul role="list" class="mt-8 space-y-3 text-sm leading-6 text-gray-600 xl:mt-10">
                        <li class="flex gap-x-3 text-base items-start">
                            <i class="fas fa-angle-right h-6 w-5 flex-none text-blue-600 mt-0.5"></i> Lamaran
                        </li>
                        <li class="flex gap-x-3 text-base items-start">
                            <i class="fas fa-angle-right h-6 w-5 flex-none text-blue-600 mt-0.5"></i> Akad Nikah
                        </li>
                        <li class="flex gap-x-3 text-base items-start">
                            <i class="fas fa-angle-right h-6 w-5 flex-none text-blue-600 mt-0.5"></i> Resepsi
                        </li>
                        <li class="flex gap-x-3 text-base items-start">
                            <i class="fas fa-angle-right h-6 w-5 flex-none text-blue-600 mt-0.5"></i> Seminar, Gathering,
                            Event, Dll.
                        </li>
                    </ul>
                </div>

                {{-- Card Exclusive --}}
                <div class="ring-2 ring-blue-600 rounded-3xl p-6 xl:p-8 bg-white">
                    <div class="flex items-center justify-between gap-x-4">
                        <h3 id="tier-exclusive" class="text-blue-600 text-2xl font-bold leading-8">Exclusive</h3>
                        <p class="rounded-full bg-blue-600/10 px-2.5 py-1 text-xs font-semibold leading-5 text-blue-600">
                            Paling Populer
                        </p>
                    </div>
                    <p class="mt-4 text-base leading-6 text-gray-600">Solusi lengkap untuk acara besar</p>
                    <p class="mt-6 flex items-end gap-x-1 mx-auto">
                        <span class="text-3xl font-bold tracking-tight text-gray-900">
                            Rp{{ number_format($minExclusivePrice, 0, ',', '.') }}</span>
                        <span class="line-through text-lg font-sans text-red-500/70">
                            Rp{{ number_format($strikeExclusivePrice, 0, ',', '.') }}</span>
                    </p>
                    <a href="{{ route('mc.show', ['id' => $mc ? $mc->id : 1, 'package' => 'exclusive']) }}"
                        aria-describedby="tier-exclusive"
                        class="bg-blue-600 text-white shadow-sm hover:bg-blue-500 mt-6 block rounded-md py-2 px-3 text-center text-base font-medium leading-6 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                        target="_blank">Pilih Paket Eksklusif</a>
                    <ul role="list" class="mt-8 space-y-3 text-sm leading-6 text-gray-600 xl:mt-10">
                        <li class="flex gap-x-3 text-base items-start">
                            <i class="fas fa-angle-right h-6 w-5 flex-none text-blue-600 mt-0.5"></i> Paket Akad & Resepsi
                        </li>
                        <li class="flex gap-x-3 text-base items-start">
                            <i class="fas fa-angle-right h-6 w-5 flex-none text-blue-600 mt-0.5"></i> Paket Lamaran, Akad &
                            Resepsi
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <div id="fitur" class="container mx-auto px-4 py-16 bg-white shadow-lg rounded-xl my-8">
        <h2 class="text-4xl font-bold text-center text-gray-800 mb-12">Mengapa Memilih Kami?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="p-6 rounded-lg text-center bg-gray-50 border border-gray-200 shadow-sm">
                <div class="text-indigo-600 text-5xl mb-4">🎤</div>
                <h3 class="text-2xl font-bold mb-2 text-gray-800">MC Profesional</h3>
                <p class="text-gray-700">MC berpengalaman dengan rekam jejak terbukti di berbagai acara.</p>
            </div>
            <div class="p-6 rounded-lg text-center bg-gray-50 border border-gray-200 shadow-sm">
                <div class="text-indigo-600 text-5xl mb-4">📅</div>
                <h3 class="text-2xl font-bold mb-2 text-gray-800">Jadwal Fleksibel</h3>
                <p class="text-gray-700">Pesan MC di hari Sabtu & Minggu sesuai ketersediaan.</p>
            </div>
            <div class="p-6 rounded-lg text-center bg-gray-50 border border-gray-200 shadow-sm">
                <div class="text-indigo-600 text-5xl mb-4">💳</div>
                <h3 class="text-2xl font-bold mb-2 text-gray-800">Pembayaran Aman</h3>
                <p class="text-gray-700">Opsi DP atau Bayar Penuh dengan Midtrans.</p>
            </div>
        </div>
    </div>

    <div id="kontak" class="container mx-auto px-4 py-16 bg-indigo-700 text-white rounded-xl my-8 shadow-lg">
        <h2 class="text-4xl font-bold text-center mb-12">Hubungi Kami</h2>
        <p class="text-lg text-center max-w-md mx-auto mb-8">
            Punya pertanyaan atau ingin berdiskusi lebih lanjut? Jangan ragu untuk menghubungi kami.
        </p>
        <div class="flex flex-col md:flex-row justify-center items-center space-y-6 md:space-y-0 md:space-x-12">
            <div class="text-center">
                <i class="fas fa-envelope text-5xl mb-3"></i>
                <p class="text-xl font-semibold">Email</p>
                <a href="mailto:info@mcbooking.com" class="text-lg hover:underline">info@mcbooking.com</a>
            </div>
            <div class="text-center">
                <i class="fas fa-phone-alt text-5xl mb-3"></i>
                <p class="text-xl font-semibold">Telepon</p>
                <a href="tel:+6281234567890" class="text-lg hover:underline">+62 812-3456-7890</a>
            </div>
            <div class="text-center">
                <i class="fab fa-whatsapp text-5xl mb-3"></i>
                <p class="text-xl font-semibold">WhatsApp</p>
                <a href="https://wa.me/6281234567890" target="_blank" class="text-lg hover:underline">Chat via
                    WhatsApp</a>
            </div>
        </div>
    </div>

@endsection

@section('footer')
    @include('layouts.footer-custom')
@endsection
