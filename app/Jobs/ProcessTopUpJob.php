<?php

namespace App\Jobs;

use App\Services\OrderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/** Memproses top-up di background (atau langsung bila QUEUE_CONNECTION=sync). */
class ProcessTopUpJob implements ShouldQueue
{
    use Queueable;

    // Top-up tidak boleh diulang otomatis: bisa menyebabkan top-up ganda di supplier nyata.
    public int $tries = 1;

    public function __construct(public int $orderId) {}

    public function handle(OrderService $orders): void
    {
        $orders->processTopUp($this->orderId);
    }
}
