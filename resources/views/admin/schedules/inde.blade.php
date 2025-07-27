{{-- resources/views/admin/schedules/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Kelola Jadwal MC')

@section('navigation')
    @include('layouts.navigation-auth') {{-- Kita akan buat ini nanti, atau sesuaikan dengan navigasi kustommu --}}
@endsection

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Kelola Jadwal Anda</h1>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Tambah/Edit Jadwal</h2>
            <form action="{{ route('admin.schedules.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="date" class="block text-gray-700 text-sm font-bold mb-2">Tanggal (Hanya
                            Sabtu/Minggu):</label>
                        <input type="date" id="date" name="date"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            required value="{{ old('date') }}">
                    </div>
                    <div>
                        <label for="start_time" class="block text-gray-700 text-sm font-bold mb-2">Waktu Mulai:</label>
                        <input type="time" id="start_time" name="start_time"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            required value="{{ old('start_time') }}">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="end_time" class="block text-gray-700 text-sm font-bold mb-2">Waktu Selesai:</label>
                        <input type="time" id="end_time" name="end_time"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            required value="{{ old('end_time') }}">
                    </div>
                    <div>
                        <label for="is_available" class="block text-gray-700 text-sm font-bold mb-2">Status
                            Ketersediaan:</label>
                        <select id="is_available" name="is_available"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            required>
                            <option value="1" {{ old('is_available', true) ? 'selected' : '' }}>Tersedia</option>
                            <option value="0" {{ old('is_available', false) ? 'selected' : '' }}>Tidak Tersedia
                                (Blokir)</option>
                        </select>
                    </div>
                </div>
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Simpan Jadwal
                </button>
            </form>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Jadwal Anda Saat Ini</h2>
            @forelse($schedules as $schedule)
                <div class="border rounded-lg p-4 mb-3 flex justify-between items-center shadow-sm">
                    <div>
                        <p class="text-lg font-semibold">{{ \Carbon\Carbon::parse($schedule->date)->format('d F Y') }}</p>
                        <p class="text-gray-700">{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} -
                            {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</p>
                        <p
                            class="text-sm
                            @if ($schedule->is_available) text-green-600
                            @else text-red-600 @endif">
                            Status: {{ $schedule->is_available ? 'Tersedia' : 'Tidak Tersedia' }}
                        </p>
                    </div>
                    <div>
                        <form action="{{ route('admin.schedules.destroy', $schedule->id) }}" method="POST"
                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded text-sm">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-gray-600">Anda belum memiliki jadwal yang diatur.</p>
            @endforelse
        </div>
    </div>
@endsection

@section('footer')
    @include('layouts.footer-custom') {{-- Kita akan buat ini nanti, atau sesuaikan dengan footer kustommu --}}
@endsection
