<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /** Pemilik pesanan atau admin. Customer lain tidak boleh melihat. */
    public function view(User $user, Order $order): bool
    {
        return $user->isAdmin() || $order->user_id === $user->id;
    }

    /** Mengubah status hanya untuk admin. */
    public function update(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }
}
