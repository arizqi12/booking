<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Pastikan ini mengarah ke model User Anda
use Illuminate\Support\Facades\Hash;
use App\Models\Mc; // Pastikan ini mengarah ke model Mc Anda

class RolesAndAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat user dengan role 'editor' (kamu)
        $editor = User::firstOrCreate(
            ['email' => 'editor@example.com'],
            [
                'name' => 'Developer Editor',
                'password' => Hash::make('password'), // GANTI DENGAN PASSWORD YANG KUAT!
                'role' => 'editor',
                'email_verified_at' => now(),
            ]
        );

        // Buat user dengan role 'admin' (MC)
        $mcUser = User::firstOrCreate(
            ['email' => 'mc@example.com'],
            [
                'name' => 'MC Utama Anda',
                'password' => Hash::make('password'), // GANTI DENGAN PASSWORD YANG KUAT!
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Jika user MC baru dibuat, buatkan juga entri di tabel 'mcs'
        if ($mcUser->wasRecentlyCreated) {
            Mc::firstOrCreate(
                ['user_id' => $mcUser->id],
                [
                    'bio' => 'MC profesional dengan pengalaman lebih dari 10 tahun di berbagai acara.',
                    'rates_per_hour' => 500000.00, // Contoh harga
                    'min_duration_hours' => 2.0,
                    'contact_phone' => '081234567890',
                    'profile_picture_url' => 'https://via.placeholder.com/150', // Contoh URL gambar
                ]
            );
        }

        $this->command->info('Roles and initial users/MC seeded successfully!');
    }
}