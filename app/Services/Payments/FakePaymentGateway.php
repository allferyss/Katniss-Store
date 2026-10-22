<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Models\Order;
use Illuminate\Support\Str;

/**
 * Gateway SIMULASI untuk demo. Tidak menghubungi pihak luar dan tidak memproses uang nyata.
 */
class FakePaymentGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'fake';
    }

    public function isSimulation(): bool
    {
        return true;
    }

    public function createPayment(Order $order, string $method): PaymentIntent
    {
        return new PaymentIntent(
            reference: 'SIM-'.Str::upper(Str::random(12)),
            driver: $this->name(),
            instructions: 'SIMULASI: tidak ada pembayaran nyata. Gunakan tombol simulasi di halaman pesanan.',
        );
    }

    public function verifyWebhookSignature(string $rawBody, ?string $signature): bool
    {
        $secret = (string) config('katniss.payment.webhook_secret');

        // Tanpa rahasia terkonfigurasi, webhook selalu ditolak (aman secara default).
        if ($secret === '' || blank($signature)) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $rawBody, $secret), $signature);
    }
}
