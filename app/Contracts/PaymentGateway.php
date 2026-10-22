<?php

namespace App\Contracts;

use App\Models\Order;
use App\Services\Payments\PaymentIntent;

/**
 * Antarmuka penyedia pembayaran. Implementasi nyata (Midtrans, Xendit, dsb.)
 * cukup mengimplementasikan kontrak ini, lalu didaftarkan di AppServiceProvider.
 */
interface PaymentGateway
{
    /** Nama driver, contoh: "fake". */
    public function name(): string;

    /** true bila hanya simulasi (tidak ada uang nyata). */
    public function isSimulation(): bool;

    /** Membuat transaksi pembayaran di penyedia dan mengembalikan referensinya. */
    public function createPayment(Order $order, string $method): PaymentIntent;

    /** Verifikasi tanda tangan webhook dari body mentah request. */
    public function verifyWebhookSignature(string $rawBody, ?string $signature): bool;
}
