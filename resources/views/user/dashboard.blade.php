@extends('layouts.app')

@section('title', 'Dashboard Pengguna')

@section('navigation')
    @include('layouts.navigation-auth')
@endsection

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Selamat Datang, {{ Auth::user()->name }}!</h1>
        <p class="text-gray-700 mb-4">Ini adalah dashboard pengguna Anda. Anda dapat melihat pemesanan Anda dan memesan MC.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-3">Pemesanan Saya</h2>
                <p class="text-gray-600 mb-4">Lihat daftar pemesanan yang telah Anda buat.</p>
                <a href="{{ route('my.bookings.index') }}"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Lihat Pemesanan</a>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-3">Booking MC</h2>
                <p class="text-gray-600 mb-4">Temukan dan pesan MC untuk acara Anda berikutnya.</p>
                <a href="{{ route('mc.show', ['id' => 1]) }}"
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Booking Sekarang</a>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('layouts.footer-custom')
@endsection
