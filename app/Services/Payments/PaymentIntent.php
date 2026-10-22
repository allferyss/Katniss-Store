<?php

namespace App\Services\Payments;

/** Hasil pembuatan transaksi pembayaran dari sebuah gateway. */
final class PaymentIntent
{
    public function __construct(
        public readonly string $reference,
        public readonly string $driver,
        public readonly ?string $instructions = null,
    ) {}
}
