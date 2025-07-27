@extends('layouts.app')

@section('title', 'Pemesan') {{-- Sesuaikan Judulnya --}}

@section('navigation')
    @include('layouts.navigation-auth') {{-- Jika halaman butuh navigasi setelah login --}}
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-2xl font-bold mb-6">Daftar Pemesanan Anda</h3>

                @forelse($bookings as $booking)
                    <div class="border rounded-lg p-4 mb-4 shadow-sm">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="text-lg font-semibold">Pemesanan #{{ $booking->id }}</h4>
                            <span
                                class="px-3 py-1 rounded-full text-sm font-semibold
                                @if ($booking->booking_status === 'confirmed') bg-green-100 text-green-800
                                @elseif($booking->booking_status === 'pending_confirmation') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ Str::title(str_replace('_', ' ', $booking->booking_status)) }}
                            </span>
                        </div>
                        <p class="text-gray-700">MC: {{ $booking->mc->user->name }}</p>
                        <p class="text-gray-700">Tanggal Acara:
                            {{ \Carbon\Carbon::parse($booking->event_date)->format('d F Y') }}</p>
                        <p class="text-gray-700">Waktu:
                            {{ \Carbon\Carbon::parse($booking->event_start_time)->format('H:i') }} -
                            {{ \Carbon\Carbon::parse($booking->event_end_time)->format('H:i') }}</p>
                        <p class="text-gray-700">Total: Rp {{ number_format($booking->grand_total, 0, ',', '.') }}</p>
                        <p class="text-gray-700">Status Pembayaran:
                            <span
                                class="font-semibold
                                @if ($booking->payment_status === 'fully_paid') text-green-600
                                @elseif($booking->payment_status === 'dp_paid') text-blue-600
                                @else text-red-600 @endif">
                                {{ Str::title(str_replace('_', ' ', $booking->payment_status)) }}
                            </span>
                        </p>
                        <div class="mt-4">
                            <a href="{{ route('my.bookings.show', $booking->id) }}"
                                class="text-indigo-600 hover:text-indigo-800 font-semibold">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-600">Anda belum memiliki pemesanan.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('layouts.footer-custom') {{-- Footer kustom --}}
@endsection
