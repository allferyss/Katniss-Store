<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin') · {{ setting('store_name', 'Katniss Store') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $menu = [
        ['admin.dashboard', 'Dashboard', 'admin.dashboard'],
        ['admin.orders.index', 'Pesanan', 'admin.orders.*'],
        ['admin.games.index', 'Game', 'admin.games.*'],
        ['admin.products.index', 'Produk', 'admin.products.*'],
        ['admin.promotions.index', 'Promo', 'admin.promotions.*'],
        ['admin.users.index', 'Pengguna', 'admin.users.*'],
        ['admin.reports.index', 'Laporan', 'admin.reports.*'],
        ['admin.settings.edit', 'Pengaturan', 'admin.settings.*'],
        ['admin.activity-logs.index', 'Log Aktivitas', 'admin.activity-logs.*'],
    ];
@endphp
<body class="min-h-full bg-ink font-sans text-slate-200 antialiased">
    <a href="#main" class="skip-link">Lewati ke konten utama</a>

    <div class="flex min-h-screen flex-col lg:flex-row">
        <aside class="border-b border-line bg-surface lg:min-h-screen lg:w-60 lg:shrink-0 lg:border-b-0 lg:border-r">
            <div class="flex items-center justify-between px-4 py-4">
                <a href="{{ route('admin.dashboard') }}" class="text-lg font-bold text-white">{{ setting('store_name', 'Katniss Store') }} <span class="block text-xs font-normal text-slate-400">Panel Admin</span></a>
                <button type="button" class="btn-secondary btn-sm lg:hidden" data-menu-toggle aria-controls="admin-menu" aria-expanded="false">Menu</button>
            </div>
            <nav id="admin-menu" class="hidden flex-col gap-1 px-3 pb-4 lg:flex" aria-label="Menu admin">
                @foreach ($menu as [$route, $label, $pattern])
                    <a href="{{ route($route) }}" class="nav-link" @if(request()->routeIs($pattern)) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
                <hr class="my-2 border-line">
                <a href="{{ route('home') }}" class="nav-link">← Lihat situs</a>
                <form method="POST" action="{{ route('logout') }}">@csrf <button type="submit" class="nav-link w-full text-left">Keluar</button></form>
            </nav>
        </aside>

        <div class="min-w-0 flex-1">
            @if (is_simulation())
                <div class="border-b border-amber-500/30 bg-amber-500/10 px-4 py-2 text-center text-xs text-amber-200" role="note">
                    <strong>MODE SIMULASI / DEMO</strong> · Pembayaran dan top-up tidak nyata.
                </div>
            @endif
            <main id="main" class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6">
                <x-flash />
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                    <h1 class="text-2xl">@yield('heading', 'Admin')</h1>
                    @yield('actions')
                </div>
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
