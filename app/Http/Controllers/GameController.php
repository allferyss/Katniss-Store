<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $games = Game::active()
            ->withMin(['products as min_price' => fn ($q) => $q->where('is_active', true)], 'price')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('publisher', 'like', "%{$search}%")))
            ->orderBy('sort_order')->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('games.index', compact('games', 'search'));
    }

    public function show(Game $game)
    {
        abort_unless($game->is_active, 404);

        $products = $game->products()->active()->orderBy('sort_order')->orderBy('price')->get();

        return view('games.show', compact('game', 'products'));
    }
}
