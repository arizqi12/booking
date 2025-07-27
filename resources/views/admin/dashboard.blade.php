@extends('layouts.app')

@section('title', 'Dashboard Princess') {{-- Sesuaikan Judulnya --}}

@section('navigation')
    @include('layouts.navigation-auth') {{-- Jika halaman butuh navigasi setelah login --}}
@endsection

@section('content')

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-2xl font-bold mb-6">Ringkasan</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-blue-50 p-6 rounded-lg shadow-md">
                        <h4 class="text-lg font-semibold text-blue-800">Pemesanan Menunggu Konfirmasi</h4>
                        <p class="text-4xl font-bold text-blue-600 mt-2">{{ $pendingBookings }}</p>
                        <a href="{{ route('admin.bookings.index') }}?status=pending_confirmation"
                            class="text-blue-500 hover:text-blue-700 mt-4 inline-block">Lihat Detail &rarr;</a>
                    </div>
                    <div class="bg-green-50 p-6 rounded-lg shadow-md">
                        <h4 class="text-lg font-semibold text-green-800">Pemesanan Dikonfirmasi</h4>
                        <p class="text-4xl font-bold text-green-600 mt-2">{{ $confirmedBookings }}</p>
                        <a href="{{ route('admin.bookings.index') }}?status=confirmed"
                            class="text-green-500 hover:text-green-700 mt-4 inline-block">Lihat Detail &rarr;</a>
                    </div>
                    <div class="bg-purple-50 p-6 rounded-lg shadow-md">
                        <h4 class="text-lg font-semibold text-purple-800">Kelola Jadwal</h4>
                        <p class="text-gray-600 mt-2">Atur ketersediaan Anda.</p>
                        <a href="#" class="text-purple-500 hover:text-purple-700 mt-4 inline-block">Kelola
                            &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('layouts.footer-custom') {{-- Footer kustom --}}
@endsection
