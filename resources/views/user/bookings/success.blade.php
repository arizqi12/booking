{{-- resources/views/booking/success.blade.php --}}

@extends('layouts.app')

@section('title', 'Pemesanan Berhasil!')

@section('navigation')
    {{-- Navigasi untuk pengguna yang sudah login --}}
    @include('layouts.navigation-auth')
@endsection

@section('content')
    <div class="min-h-screen flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-2xl">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden text-center p-8">
                <div class="text-green-500 mb-6">
                    <i class="fas fa-check-circle text-6xl"></i>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Pemesanan Berhasil Dibuat!</h1>
                <p class="text-lg text-gray-700 mb-6">
                    Terima kasih, <span class="font-semibold">{{ $booking->user->name }}</span>! Pesanan Anda untuk MC <span
                        class="font-semibold">{{ $booking->mc->user->name }}</span> telah berhasil dibuat.
                </p>

                <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-700 p-4 mb-6 rounded-md" role="alert">
                    <p class="font-bold text-xl mb-2">Nomor Pemesanan Anda:</p>
                    <p class="text-3xl font-extrabold">{{ $booking->id }}</p>
                </div>

                <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 text-left mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Detail Pembayaran:</h2>
                    <div class="grid grid-cols-2 gap-4 text-gray-700 mb-4">
                        <div>
                            <p class="font-semibold">Total Biaya Layanan MC:</p>
                            <p class="font-semibold">Biaya Layanan Website:</p>
                            <p class="font-semibold text-xl">Grand Total:</p>
                        </div>
                        <div class="text-right">
                            <p>Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</p>
                            <p>Rp {{ number_format($booking->service_fee, 0, ',', '.') }}</p>
                            <p class="font-bold text-xl text-indigo-700">Rp
                                {{ number_format($booking->grand_total, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    @if ($booking->payment_status === 'pending_dp')
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-md mb-4">
                            <p class="font-bold mb-2">Pembayaran Anda memerlukan Down Payment.</p>
                            <p>Jumlah yang harus dibayar sekarang: <span class="text-2xl font-bold">Rp
                                    {{ number_format($booking->dp_required_amount, 0, ',', '.') }}</span></p>
                        </div>
                    @else
                        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded-md mb-4">
                            <p class="font-bold mb-2">Pembayaran Anda memerlukan Pelunasan Penuh.</p>
                            <p>Jumlah yang harus dibayar sekarang: <span class="text-2xl font-bold">Rp
                                    {{ number_format($booking->grand_total, 0, ',', '.') }}</span></p>
                        </div>
                    @endif
                </div>

                <p class="text-gray-600 mb-8">
                    Silakan lanjutkan pembayaran Anda. Detail instruksi akan dikirimkan ke email Anda.
                </p>

                <button id="pay-button"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-full text-lg shadow-md hover:shadow-lg transition duration-300 inline-block"
                    data-booking-id="{{ $booking->id }}"> {{-- Menggunakan data-booking-id untuk JavaScript --}}
                    <i class="fas fa-wallet mr-2"></i> Lanjutkan Pembayaran
                </button>

                <div class="mt-8 text-sm text-gray-500">
                    <p>Anda bisa melihat status pesanan Anda di:</p>
                    <a href="{{ route('my.bookings.show', $booking->id) }}"
                        class="text-indigo-600 hover:underline font-semibold mt-1 inline-block">Halaman Detail Pesanan
                        Saya</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('layouts.footer-custom')
@endsection

{{-- Script untuk Midtrans Snap dipisah ke resources/js/Pages/Booking/Success.js --}}
