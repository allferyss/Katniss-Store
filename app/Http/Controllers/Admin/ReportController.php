<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$filters, $query] = $this->filtered($request);

        $summary = [
            'count' => (clone $query)->count(),
            'gross' => (int) (clone $query)->sum('total'),
            'paid' => (int) (clone $query)->where('payment_status', PaymentStatus::Paid->value)->sum('total'),
            'discount' => (int) (clone $query)->where('payment_status', PaymentStatus::Paid->value)->sum('discount_amount'),
        ];

        $orders = $query->with(['user', 'items'])->latest()->paginate(25)->withQueryString();

        return view('admin.reports.index', compact('orders', 'filters', 'summary'));
    }

    public function export(Request $request)
    {
        [$filters, $query] = $this->filtered($request);
        $filename = 'laporan-transaksi-'.$filters['from'].'-sd-'.$filters['to'].'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
            fputcsv($out, ['No. Order', 'Tanggal', 'Customer', 'Email', 'Game', 'Produk', 'Player ID', 'Server ID', 'Subtotal', 'Diskon', 'Total', 'Status Pesanan', 'Status Pembayaran', 'Metode']);

            $query->with(['user', 'items'])->orderBy('id')->chunk(500, function ($chunk) use ($out) {
                foreach ($chunk as $order) {
                    $item = $order->items->first();
                    fputcsv($out, array_map([self::class, 'safeCell'], [
                        $order->order_number,
                        $order->created_at->format('Y-m-d H:i:s'),
                        $order->user?->name,
                        $order->user?->email,
                        $item?->game_name,
                        $item?->product_name,
                        $order->player_id,
                        $order->server_id,
                        $order->subtotal,
                        $order->discount_amount,
                        $order->total,
                        $order->status->label(),
                        $order->payment_status->label(),
                        $order->paymentMethodLabel(),
                    ]));
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Cegah CSV/formula injection di Excel: sel yang diawali = + - @ diberi tanda kutip. */
    public static function safeCell(mixed $value): mixed
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }

    /** @return array{0: array<string, string|null>, 1: Builder} */
    private function filtered(Request $request): array
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'status' => ['nullable', Rule::enum(OrderStatus::class)],
            'payment_status' => ['nullable', Rule::enum(PaymentStatus::class)],
        ]);

        $filters = [
            'from' => $request->query('from') ?: now()->subDays(29)->toDateString(),
            'to' => $request->query('to') ?: now()->toDateString(),
            'status' => $request->query('status') ?: null,
            'payment_status' => $request->query('payment_status') ?: null,
        ];

        $query = Order::query()
            ->whereDate('created_at', '>=', $filters['from'])
            ->whereDate('created_at', '<=', $filters['to'])
            ->when($filters['status'], fn ($q, $v) => $q->where('status', $v))
            ->when($filters['payment_status'], fn ($q, $v) => $q->where('payment_status', $v));

        return [$filters, $query];
    }
}
