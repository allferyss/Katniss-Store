<?php

namespace App\Services\TopUp;

use App\Contracts\TopUpProvider;
use App\Models\Order;
use Illuminate\Support\Str;

/**
 * Supplier top-up SIMULASI. Tidak ada top-up nyata yang dikirim ke game.
 * Untuk mencoba jalur gagal: gunakan Player ID "000000".
 */
class FakeTopUpProvider implements TopUpProvider
{
    public function name(): string
    {
        return 'fake';
    }

    public function isSimulation(): bool
    {
        return true;
    }

    public function topUp(Order $order): TopUpResult
    {
        if ($order->player_id === '000000') {
            return new TopUpResult(false, null, 'SIMULASI: Player ID tidak ditemukan.');
        }

        return new TopUpResult(true, 'SIMTOPUP-'.Str::upper(Str::random(10)), 'SIMULASI: top-up berhasil (tidak ada top-up nyata).');
    }
}
