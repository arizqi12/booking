<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mc; // Pastikan ini diimpor
use App\Models\McService; // Pastikan ini diimpor

class HomeController extends Controller
{
    /**
     * Display the application's home page.
     */
    public function index()
    {
        // Ambil data MC (asumsi hanya ada satu MC)
        $mc = Mc::with('user')->first();

        // Ambil semua layanan MC yang aktif dari database
        // dan kunci hasilnya berdasarkan slug untuk akses mudah di view
        $allMcServices = McService::active()->get()->keyBy('slug');

        // Jika data layanan tidak ada, berikan nilai default atau kosong
        $lamaranPrice = $allMcServices['lamaran']->price ?? 0;
        $akadNikahPrice = $allMcServices['akad_nikah']->price ?? 0;
        $resepsiPrice = $allMcServices['resepsi']->price ?? 0;
        $otherEventsPrice = $allMcServices['other_events']->price ?? 0;
        $paketAkadResepsiPrice = $allMcServices['paket_akad_resepsi']->price ?? 0;
        $paketFullWeddingPrice = $allMcServices['paket_full_wedding']->price ?? 0;

        // Hitung harga "mulai dari" untuk Paket Standar dan Paket Eksklusif
        $minStandardPrice = min($lamaranPrice, $akadNikahPrice, $resepsiPrice, $otherEventsPrice);
        // Harga coret bisa disimulasikan sebagai harga tertinggi dari individual + sedikit markup
        $strikeStandardPrice = max($lamaranPrice, $akadNikahPrice, $resepsiPrice, $otherEventsPrice) + 100000; 

        $minExclusivePrice = min($paketAkadResepsiPrice, $paketFullWeddingPrice);
        // Harga coret bisa disimulasikan sebagai harga tertinggi dari paket + sedikit markup
        $strikeExclusivePrice = max($paketAkadResepsiPrice, $paketFullWeddingPrice) + 850000;

        // Kirim semua data yang dibutuhkan ke view
        return view('welcome', compact(
            'mc',
            'lamaranPrice',
            'akadNikahPrice',
            'resepsiPrice',
            'otherEventsPrice',
            'paketAkadResepsiPrice',
            'paketFullWeddingPrice',
            'minStandardPrice',
            'strikeStandardPrice',
            'minExclusivePrice',
            'strikeExclusivePrice'
        ));
    }
}