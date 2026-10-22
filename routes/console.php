<?php

use Illuminate\Support\Facades\Schedule;

// Membatalkan pesanan yang belum dibayar. Butuh scheduler: * * * * * php artisan schedule:run
Schedule::command('katniss:cancel-expired-orders')->hourly()->withoutOverlapping();
