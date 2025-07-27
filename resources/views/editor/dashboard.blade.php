@extends('layouts.app')

@section('title', 'Dashboard Editor') {{-- Sesuaikan Judulnya --}}

@section('navigation')
    @include('layouts.navigation-auth') {{-- Jika halaman butuh navigasi setelah login --}}
@endsection

@section('content')

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-2xl font-bold mb-6">Ringkasan Sistem</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-purple-50 p-6 rounded-lg shadow-md">
                        <h4 class="text-lg font-semibold text-purple-800">Total Pengguna</h4>
                        <p class="text-4xl font-bold text-purple-600 mt-2">{{ $totalUsers }}</p>
                        <a href="#" class="text-purple-500 hover:text-purple-700 mt-4 inline-block">Kelola
                            Pengguna &rarr;</a>
                    </div>
                    <div class="bg-orange-50 p-6 rounded-lg shadow-md">
                        <h4 class="text-lg font-semibold text-orange-800">Total Pemesanan</h4>
                        <p class="text-4xl font-bold text-orange-600 mt-2">{{ $totalBookings }}</p>
                        <a href="#" class="text-orange-500 hover:text-orange-700 mt-4 inline-block">Kelola
                            Pemesanan &rarr;</a>
                    </div>
                    <div class="bg-teal-50 p-6 rounded-lg shadow-md">
                        <h4 class="text-lg font-semibold text-teal-800">Total Pendapatan (Nett)</h4>
                        <p class="text-4xl font-bold text-teal-600 mt-2">Rp
                            {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                        <a href="#" class="text-teal-500 hover:text-teal-700 mt-4 inline-block">Lihat Laporan
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
