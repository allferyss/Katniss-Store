<?php

namespace App\Http\Controllers;

use App\Models\Game;

class HomeController extends Controller
{
    public function index()
    {
        $games = Game::active()
            ->withMin(['products as min_price' => fn ($q) => $q->where('is_active', true)], 'price')
            ->orderBy('sort_order')->orderBy('name')
            ->limit(8)
            ->get();

        return view('home', compact('games'));
    }
}
