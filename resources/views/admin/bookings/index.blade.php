@extends('layouts.app')

@section('title', 'Pesanan Masuk') {{-- Sesuaikan Judulnya --}}

@section('navigation')
    @include('layouts.navigation-auth') {{-- Jika halaman butuh navigasi setelah login --}}
@endsection

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-2xl font-bold mb-6">Daftar Pemesanan Anda</h3>

            @forelse($bookings as $booking)
                <div class="border rounded-lg p-4 mb-4 shadow-sm">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="text-lg font-semibold">Pemesanan #{{ $booking->id }}</h4>
                        <div class="flex items-center space-x-2">
                            <span
                                class="px-3 py-1 rounded-full text-sm font-semibold
                                    @if ($booking->booking_status === 'confirmed') bg-green-100 text-green-800
                                    @elseif($booking->booking_status === 'pending_confirmation') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                {{ Str::title(str_replace('_', ' ', $booking->booking_status)) }}
                            </span>
                            <span
                                class="px-3 py-1 rounded-full text-sm font-semibold
                                    @if ($booking->payment_status === 'fully_paid') bg-green-100 text-green-800
                                    @elseif($booking->payment_status === 'dp_paid') bg-blue-100 text-blue-800
                                    @else bg-red-100 text-red-800 @endif">
                                Bayar: {{ Str::title(str_replace('_', ' ', $booking->payment_status)) }}
                            </span>
                        </div>
                    </div>
                    <p class="text-gray-700">Pemesan: {{ $booking->user->name }}</p>
                    <p class="text-gray-700">Tanggal Acara:
                        {{ \Carbon\Carbon::parse($booking->event_date)->format('d F Y') }}</p>
                    <p class="text-gray-700">Waktu:
                        {{ \Carbon\Carbon::parse($booking->event_start_time)->format('H:i') }} -
                        {{ \Carbon\Carbon::parse($booking->event_end_time)->format('H:i') }}</p>
                    <p class="text-gray-700">Total: Rp {{ number_format($booking->grand_total, 0, ',', '.') }}</p>
                    <p class="text-gray-700">Sudah Dibayar: Rp
                        {{ number_format($booking->paid_amount, 0, ',', '.') }}</p>
                    <p class="text-gray-700">Sisa Pembayaran: Rp
                        {{ number_format($booking->remaining_amount, 0, ',', '.') }}</p>
                    <div class="mt-4 flex space-x-2">
                        <a href="{{ route('admin.bookings.show', $booking->id) }}"
                            class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-1 px-3 rounded text-sm">
                            Lihat Detail
                        </a>
                        @if ($booking->booking_status === 'pending_confirmation')
                            <form action="{{ route('admin.bookings.confirm', $booking->id) }}" method="POST"
                                onsubmit="return confirm('Apakah Anda yakin ingin mengkonfirmasi pemesanan ini?');">
                                @csrf
                                <button type="submit"
                                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-3 rounded text-sm">
                                    Konfirmasi
                                </button>
                            </form>
                            <button type="button"
                                class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded text-sm"
                                onclick="openRejectModal({{ $booking->id }})">
                                Tolak
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-gray-600">Belum ada pemesanan masuk.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Modal Tolak Pemesanan (Akan dibuat di layout atau sebagai komponen terpisah) --}}
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-xl w-96">
        <h3 class="text-xl font-bold mb-4">Tolak Pemesanan</h3>
        <form id="rejectForm" method="POST">
            @csrf
            <input type="hidden" name="_method" value="POST"> {{-- Pastikan method POST untuk form --}}
            <div class="mb-4">
                <label for="reason" class="block text-gray-700 text-sm font-bold mb-2">Alasan Penolakan:</label>
                <textarea id="reason" name="reason" rows="4"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    required></textarea>
            </div>
            <div class="flex justify-end space-x-4">
                <button type="button" onclick="closeRejectModal()"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">Batal</button>
                <button type="submit"
                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Tolak</button>
            </div>
        </form>
    </div>
</div>
@endsection
@section('footer')
@include('layouts.footer-custom') {{-- Footer kustom --}}
@endsection
