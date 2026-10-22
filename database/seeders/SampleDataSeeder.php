<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Game;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * DATA CONTOH untuk development/demo. Nama game fiktif.
 * Jalankan manual bila perlu:  php artisan db:seed --class=SampleDataSeeder
 * Akun demo di bawah HANYA untuk development: hapus/ganti sebelum production.
 */
class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->users();
        $this->catalog();

        Promotion::firstOrCreate(['code' => 'DEMO10'], [
            'name' => 'Promo demo 10%', 'type' => Promotion::TYPE_PERCENT, 'value' => 10,
            'min_purchase' => 20000, 'max_discount' => 10000, 'usage_limit' => 100, 'is_active' => true,
        ]);
    }

    private function users(): void
    {
        $admin = User::firstOrNew(['email' => 'admin@katnissstore.test']);
        if (! $admin->exists) {
            $admin->fill(['name' => 'Admin Demo', 'password' => 'Admin12345']);
            $admin->role = Role::Admin;
            $admin->email_verified_at = now();
            $admin->save();
        }

        $customer = User::firstOrNew(['email' => 'customer@katnissstore.test']);
        if (! $customer->exists) {
            $customer->fill(['name' => 'Customer Demo', 'password' => 'Customer12345']);
            $customer->role = Role::Customer;
            $customer->email_verified_at = now();
            $customer->save();
        }
    }

    private function catalog(): void
    {
        $games = [
            [
                'name' => 'Dragon Realm Online', 'slug' => 'dragon-realm-online', 'publisher' => 'Contoh Studio',
                'description' => 'Game fiktif untuk demo. RPG fantasi dengan mata uang permata.',
                'id_hint' => "Buka menu Profil, ID pemain ada di bawah nama karakter.\nServer ID tampil di layar pemilihan server.",
                'requires_server_id' => true, 'sort_order' => 1,
                'products' => [['60 Permata', 15000], ['120 Permata', 29000], ['300 Permata', 72000], ['600 Permata', 140000]],
            ],
            [
                'name' => 'Cyber Strike Mobile', 'slug' => 'cyber-strike-mobile', 'publisher' => 'Contoh Studio',
                'description' => 'Game fiktif untuk demo. Tembak-menembak tim dengan mata uang kredit.',
                'id_hint' => 'ID pemain terlihat di pojok kiri atas layar utama.',
                'requires_server_id' => false, 'sort_order' => 2,
                'products' => [['100 Kredit', 10000], ['310 Kredit', 30000], ['520 Kredit', 50000]],
            ],
            [
                'name' => 'Battle Arena Legends', 'slug' => 'battle-arena-legends', 'publisher' => 'Contoh Studio',
                'description' => 'Game fiktif untuk demo. MOBA 5 lawan 5.',
                'id_hint' => 'Ketuk foto profil, ID dan Server ID tampil di halaman profil.',
                'requires_server_id' => true, 'sort_order' => 3,
                'products' => [['86 Berlian', 20000], ['172 Berlian', 40000], ['257 Berlian', 60000]],
            ],
        ];

        foreach ($games as $data) {
            $products = $data['products'];
            unset($data['products']);

            $game = Game::firstOrCreate(['slug' => $data['slug']], $data + ['is_active' => true]);

            foreach ($products as $i => [$name, $price]) {
                $game->products()->firstOrCreate(['name' => $name], [
                    'price' => $price, 'cost_price' => (int) round($price * 0.92), 'is_active' => true, 'sort_order' => $i,
                ]);
            }
        }
    }
}
