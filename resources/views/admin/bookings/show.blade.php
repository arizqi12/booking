@extends('layouts.app')

@section('title', 'Detail Pemesanan') {{-- Sesuaikan Judulnya --}}

@section('navigation')
    @include('layouts.navigation-auth') {{-- Jika halaman butuh navigasi setelah login --}}
@endsection

<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-2xl font-bold mb-6">Informasi Pemesanan</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-lg mb-6">
                <div>
                    <p><span class="font-semibold">Pemesan:</span> {{ $booking->user->name }}</p>
                    <p><span class="font-semibold">Email Pemesan:</span> {{ $booking->user->email }}</p>
                    <p><span class="font-semibold">Tanggal Acara:</span>
                        {{ \Carbon\Carbon::parse($booking->event_date)->format('d F Y') }}</p>
                    <p><span class="font-semibold">Waktu:</span>
                        {{ \Carbon\Carbon::parse($booking->event_start_time)->format('H:i') }} -
                        {{ \Carbon\Carbon::parse($booking->event_end_time)->format('H:i') }}</p>
                    <p><span class="font-semibold">Jenis Acara:</span> {{ $booking->event_type }}</p>
                    <p><span class="font-semibold">Lokasi:</span> {{ $booking->location }}</p>
                </div>
                <div>
                    <p><span class="font-semibold">Total Biaya MC:</span> Rp
                        {{ number_format($booking->total_amount, 0, ',', '.') }}</p>
                    <p><span class="font-semibold">Biaya Layanan:</span> Rp
                        {{ number_format($booking->service_fee, 0, ',', '.') }}</p>
                    <p><span class="font-semibold">Grand Total:</span> Rp
                        {{ number_format($booking->grand_total, 0, ',', '.') }}</p>
                    <p><span class="font-semibold">DP Dibutuhkan (50%):</span> Rp
                        {{ number_format($booking->dp_required_amount, 0, ',', '.') }}</p>
                    <p><span class="font-semibold">Sudah Dibayar:</span> Rp
                        {{ number_format($booking->paid_amount, 0, ',', '.') }}</p>
                    <p><span class="font-semibold">Sisa Pembayaran:</span> Rp
                        {{ number_format($booking->remaining_amount, 0, ',', '.') }}</p>
                </div>
            </div>

            <p class="text-lg mb-2"><span class="font-semibold">Catatan:</span> {{ $booking->notes ?? '-' }}</p>
            <p class="text-lg mb-6"><span class="font-semibold">Status Booking:</span>
                <span
                    class="px-3 py-1 rounded-full text-base font-semibold
                        @if ($booking->booking_status === 'confirmed') bg-green-100 text-green-800
                        @elseif($booking->booking_status === 'pending_confirmation') bg-yellow-100 text-yellow-800
                        @else bg-gray-100 text-gray-800 @endif">
                    {{ Str::title(str_replace('_', ' ', $booking->booking_status)) }}
                </span>
                @if ($booking->booking_status === 'rejected' && $booking->cancellation_reason)
                    <span class="text-sm text-gray-600 ml-2">(Alasan: {{ $booking->cancellation_reason }})</span>
                @endif
            </p>
            <p class="text-lg mb-6"><span class="font-semibold">Status Pembayaran:</span>
                <span
                    class="px-3 py-1 rounded-full text-base font-semibold
                        @if ($booking->payment_status === 'fully_paid') bg-green-100 text-green-800
                        @elseif($booking->payment_status === 'dp_paid') bg-blue-100 text-blue-800
                        @else bg-red-100 text-red-800 @endif">
                    {{ Str::title(str_replace('_', ' ', $booking->payment_status)) }}
                </span>
            </p>

            @if ($booking->booking_status === 'pending_confirmation')
                <div class="mt-8 flex space-x-4">
                    <form action="{{ route('admin.bookings.confirm', $booking->id) }}" method="POST"
                        onsubmit="return confirm('Apakah Anda yakin ingin mengkonfirmasi pemesanan ini?');">
                        @csrf
                        <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Konfirmasi Pemesanan
                        </button>
                    </form>
                    <button type="button" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                        onclick="openRejectModal({{ $booking->id }})">
                        Tolak Pemesanan
                    </button>
                </div>
            @endif

            <h4 class="text-xl font-bold mt-8 mb-4">Riwayat Pembayaran</h4>
            @forelse($booking->payments as $payment)
                <div class="border rounded-lg p-3 mb-3 text-sm">
                    <p><span class="font-semibold">Jumlah:</span> Rp
                        {{ number_format($payment->amount, 0, ',', '.') }}
                        ({{ Str::title(str_replace('_', ' ', $payment->payment_type)) }})
                    </p>
                    <p><span class="font-semibold">Metode:</span> {{ $payment->payment_method ?? 'N/A' }}</p>
                    <p><span class="font-semibold">Status Midtrans:</span>
                        {{ Str::title($payment->midtrans_status) }}</p>
                    <p><span class="font-semibold">Waktu Transaksi:</span>
                        {{ $payment->transaction_time->format('d M Y H:i') }}</p>
                    <p class="text-xs text-gray-500">ID Transaksi: {{ $payment->midtrans_transaction_id }}</p>
                </div>
            @empty
                <p class="text-gray-600">Belum ada riwayat pembayaran.</p>
            @endforelse

            <div class="mt-6 flex justify-end">
                <a href="{{ route('admin.bookings.index') }}" class="text-gray-600 hover:text-gray-800 font-semibold">
                    Kembali ke Daftar Pemesanan
                </a>
            </div>
        </div>
    </div>
</div>
{{-- Modal Tolak Pemesanan --}}
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-xl w-96">
        <h3 class="text-xl font-bold mb-4">Tolak Pemesanan</h3>
        <form id="rejectForm" method="POST">
            @csrf
            <input type="hidden" name="_method" value="POST">
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
