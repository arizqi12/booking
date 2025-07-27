<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\McService;

class McServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Layanan Individual
        McService::firstOrCreate(['slug' => 'lamaran'], [
            'name' => 'Lamaran',
            'type' => 'individual',
            'price' => 650000.00,
            'description' => 'Jasa MC untuk acara lamaran.',
            'is_active' => true,
        ]);

        McService::firstOrCreate(['slug' => 'akad_nikah'], [
            'name' => 'Akad Nikah',
            'type' => 'individual',
            'price' => 800000.00,
            'description' => 'Jasa MC untuk acara akad nikah.',
            'is_active' => true,
        ]);

        McService::firstOrCreate(['slug' => 'resepsi'], [
            'name' => 'Resepsi',
            'type' => 'individual',
            'price' => 800000.00,
            'description' => 'Jasa MC untuk acara resepsi pernikahan.',
            'is_active' => true,
        ]);

        McService::firstOrCreate(['slug' => 'other_events'], [
            'name' => 'Seminar, Gathering, Event, Birthday, Dll.',
            'type' => 'individual',
            'price' => 700000.00,
            'description' => 'Jasa MC untuk berbagai acara non-pernikahan.',
            'is_active' => true,
        ]);

        // Paket Layanan
        McService::firstOrCreate(['slug' => 'paket_akad_resepsi'], [
            'name' => 'Paket Akad & Resepsi',
            'type' => 'package',
            'price' => 1000000.00, // Harga paket diskon
            'description' => 'Paket MC untuk acara Akad Nikah dan Resepsi.',
            'included_services' => ['akad_nikah', 'resepsi'], // Menyimpan slug layanan yang termasuk
            'is_active' => true,
        ]);

        McService::firstOrCreate(['slug' => 'paket_full_wedding'], [
            'name' => 'Paket Lamaran, Akad & Resepsi',
            'type' => 'package',
            'price' => 1500000.00, // Harga paket diskon
            'description' => 'Paket MC lengkap untuk Lamaran, Akad Nikah, dan Resepsi.',
            'included_services' => ['lamaran', 'akad_nikah', 'resepsi'],
            'is_active' => true,
        ]);

        $this->command->info('MC Services seeded successfully!');
    }
}