<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

/** Health check ringan untuk load balancer / monitoring. Tidak membocorkan detail internal. */
class HealthController extends Controller
{
    public function __invoke()
    {
        try {
            DB::connection()->getPdo();
            $ok = true;
        } catch (\Throwable) {
            $ok = false;
        }

        return response()->json(['status' => $ok ? 'ok' : 'error'], $ok ? 200 : 503);
    }
}
