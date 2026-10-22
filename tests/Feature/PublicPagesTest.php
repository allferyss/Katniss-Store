<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_static_pages_load(): void
    {
        foreach (['/', '/games', '/cara-top-up', '/faq', '/kebijakan-privasi', '/syarat-layanan', '/kontak', '/cek-pesanan', '/masuk', '/daftar'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_health_endpoint_reports_ok(): void
    {
        $this->getJson('/health')->assertOk()->assertJson(['status' => 'ok']);
    }

    public function test_catalog_lists_only_active_games_and_supports_search(): void
    {
        Game::factory()->create(['name' => 'Alpha Quest']);
        Game::factory()->create(['name' => 'Beta Arena']);
        Game::factory()->inactive()->create(['name' => 'Hidden Game']);

        $this->get('/games')->assertSee('Alpha Quest')->assertSee('Beta Arena')->assertDontSee('Hidden Game');
        $this->get('/games?q=Alpha')->assertSee('Alpha Quest')->assertDontSee('Beta Arena');
    }

    public function test_game_detail_shows_only_active_products(): void
    {
        $game = Game::factory()->create(['name' => 'Alpha Quest']);
        Product::factory()->for($game)->create(['name' => '100 Gem Aktif']);
        Product::factory()->for($game)->inactive()->create(['name' => '999 Gem Mati']);

        $this->get(route('games.show', $game))->assertOk()->assertSee('100 Gem Aktif')->assertDontSee('999 Gem Mati');
    }

    public function test_inactive_game_detail_is_not_found(): void
    {
        $game = Game::factory()->inactive()->create();

        $this->get(route('games.show', $game))->assertNotFound();
    }

    public function test_order_tracking_shows_limited_info(): void
    {
        $order = Order::factory()->create(['player_id' => 'RAHASIA999']);

        $this->get('/cek-pesanan?order_number='.$order->order_number)
            ->assertOk()->assertSee($order->order_number)->assertDontSee('RAHASIA999');

        $this->get('/cek-pesanan?order_number=KS-TIDAK-ADA')->assertOk()->assertSee('Pesanan tidak ditemukan');
    }
}
