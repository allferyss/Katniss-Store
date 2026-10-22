<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Order;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $byStatus = Order::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $stats = [
            'orders' => (int) $byStatus->sum(),
            'pending' => (int) ($byStatus[OrderStatus::Pending->value] ?? 0),
            'processing' => (int) ($byStatus[OrderStatus::Processing->value] ?? 0),
            'completed' => (int) ($byStatus[OrderStatus::Completed->value] ?? 0),
            'failed' => (int) ($byStatus[OrderStatus::Failed->value] ?? 0),
            'cancelled' => (int) ($byStatus[OrderStatus::Cancelled->value] ?? 0),
            // Penjualan = pesanan dengan pembayaran berstatus "paid" (refund tidak dihitung).
            'sales' => (int) Order::where('payment_status', PaymentStatus::Paid->value)->sum('total'),
            'games' => Game::count(),
            'customers' => User::where('role', 'customer')->count(),
        ];

        // Penjualan 7 hari terakhir (dikelompokkan di PHP agar netral terhadap jenis database).
        $since = now()->subDays(6)->startOfDay();
        $paid = Order::where('payment_status', PaymentStatus::Paid->value)
            ->where('paid_at', '>=', $since)
            ->get(['total', 'paid_at'])
            ->groupBy(fn ($o) => $o->paid_at->format('Y-m-d'));

        $chart = collect(range(0, 6))->map(function ($i) use ($since, $paid) {
            $day = $since->copy()->addDays($i);

            return [
                'label' => $day->format('d/m'),
                'total' => (int) ($paid->get($day->format('Y-m-d'))?->sum('total') ?? 0),
            ];
        });

        $recent = Order::with(['user', 'items'])->latest()->limit(8)->get();

        return view('admin.dashboard', compact('stats', 'chart', 'recent'));
    }
}
