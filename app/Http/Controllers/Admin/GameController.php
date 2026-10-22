<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GameRequest;
use App\Models\Game;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $games = Game::withCount('products')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('sort_order')->orderBy('name')
            ->paginate(15)->withQueryString();

        return view('admin.games.index', compact('games', 'search'));
    }

    public function create()
    {
        return view('admin.games.form', ['game' => new Game(['is_active' => true, 'sort_order' => 0])]);
    }

    public function store(GameRequest $request, ActivityLogger $logger)
    {
        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            // store() membuat nama file acak.
            $data['image_path'] = $request->file('image')->store('games', config('katniss.uploads.disk'));
        }

        $game = Game::create(array_merge($data, ['sort_order' => $data['sort_order'] ?? 0]));
        $logger->log('game.created', "Menambah game {$game->name}", $game);

        return redirect()->route('admin.games.index')->with('success', 'Game berhasil ditambahkan.');
    }

    public function edit(Game $game)
    {
        return view('admin.games.form', compact('game'));
    }

    public function update(GameRequest $request, Game $game, ActivityLogger $logger)
    {
        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            $disk = config('katniss.uploads.disk');
            $new = $request->file('image')->store('games', $disk);
            if ($game->image_path) {
                Storage::disk($disk)->delete($game->image_path);
            }
            $data['image_path'] = $new;
        }

        $game->update(array_merge($data, ['sort_order' => $data['sort_order'] ?? 0]));
        $logger->log('game.updated', "Memperbarui game {$game->name}", $game);

        return redirect()->route('admin.games.index')->with('success', 'Game berhasil diperbarui.');
    }

    public function destroy(Game $game, ActivityLogger $logger)
    {
        // Riwayat pesanan aman: nama game & harga tersimpan sebagai snapshot di order_items.
        if ($game->image_path) {
            Storage::disk(config('katniss.uploads.disk'))->delete($game->image_path);
        }

        $logger->log('game.deleted', "Menghapus game {$game->name}", null, ['game_id' => $game->id]);
        $game->delete();

        return redirect()->route('admin.games.index')->with('success', 'Game dihapus (beserta produknya). Untuk menyembunyikan sementara, lebih aman menonaktifkan game.');
    }
}
