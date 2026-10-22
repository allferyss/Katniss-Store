<?php

namespace App\Contracts;

use App\Models\Order;
use App\Services\TopUp\TopUpResult;

/**
 * Antarmuka supplier top-up game. Implementasi nyata memanggil API supplier.
 */
interface TopUpProvider
{
    public function name(): string;

    public function isSimulation(): bool;

    /** Kirim top-up untuk pesanan yang sudah dibayar. */
    public function topUp(Order $order): TopUpResult;
}
