<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $counts = $user->orders()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'total' => (int) $counts->sum(),
            'active' => (int) (($counts[OrderStatus::Pending->value] ?? 0) + ($counts[OrderStatus::Processing->value] ?? 0)),
            'completed' => (int) ($counts[OrderStatus::Completed->value] ?? 0),
        ];

        $recent = $user->orders()->with('items')->latest()->limit(5)->get();

        return view('dashboard', compact('stats', 'recent'));
    }
}
