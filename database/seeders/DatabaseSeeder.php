<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Pengaturan dasar toko: aman untuk semua lingkungan.
        $this->call(SettingSeeder::class);

        // Data contoh + akun demo HANYA untuk development lokal.
        // Di production, buat admin dengan: php artisan katniss:create-admin email@anda.com
        if (app()->environment('local', 'testing')) {
            $this->call(SampleDataSeeder::class);
        }
    }
}
