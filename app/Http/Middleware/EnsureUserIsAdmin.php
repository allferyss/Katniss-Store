<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Otorisasi sisi server: hanya role admin yang boleh lewat. */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Anda tidak memiliki akses ke halaman ini.');

        return $next($request);
    }
}
