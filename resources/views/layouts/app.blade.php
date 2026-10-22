<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@hasSection('title') @yield('title') · {{ setting('store_name', 'Katniss Store') }} @else {{ setting('store_name', 'Katniss Store') }} @endif</title>
    <meta name="description" content="@yield('description', setting('tagline'))">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-ink font-sans text-slate-200 antialiased">
    <a href="#main" class="skip-link">Lewati ke konten utama</a>

    @if (is_simulation())
        <div class="border-b border-amber-500/30 bg-amber-500/10 px-4 py-2 text-center text-xs text-amber-200" role="note">
            <strong>MODE SIMULASI / DEMO</strong> · Pembayaran dan top-up pada situs ini hanya simulasi. Tidak ada uang atau item game nyata yang diproses.
        </div>
    @endif

    <header class="sticky top-0 z-30 border-b border-line bg-ink/90 backdrop-blur">
        <div class="container-page flex h-16 items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-bold text-white">
                @if (setting('logo_path'))
                    <img src="{{ upload_url(setting('logo_path')) }}" alt="" class="h-8 w-8 rounded-md object-cover">
                @else
                    <img src="{{ asset('favicon.svg') }}" alt="" class="h-8 w-8">
                @endif
                <span>{{ setting('store_name', 'Katniss Store') }}</span>
            </a>

            <nav class="hidden items-center gap-1 md:flex" aria-label="Navigasi utama">
                <a href="{{ route('home') }}" class="nav-link" @if(request()->routeIs('home')) aria-current="page" @endif>Beranda</a>
                <a href="{{ route('games.index') }}" class="nav-link" @if(request()->routeIs('games.*')) aria-current="page" @endif>Katalog</a>
                <a href="{{ route('pages.how-to') }}" class="nav-link" @if(request()->routeIs('pages.how-to')) aria-current="page" @endif>Cara Top-Up</a>
                <a href="{{ route('tracking') }}" class="nav-link" @if(request()->routeIs('tracking')) aria-current="page" @endif>Cek Pesanan</a>
            </nav>

            <div class="hidden items-center gap-2 md:flex">
                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="btn-secondary btn-sm">Panel Admin</a>
                    @endif
                    <a href="{{ route('dashboard') }}" class="btn-ghost btn-sm">Akun Saya</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf <button type="submit" class="btn-ghost btn-sm">Keluar</button></form>
                @else
                    <a href="{{ route('login') }}" class="btn-ghost btn-sm">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-primary btn-sm">Daftar</a>
                @endauth
            </div>

            <button type="button" class="btn-secondary btn-sm md:hidden" data-menu-toggle aria-controls="mobile-menu" aria-expanded="false">
                <span class="sr-only">Buka menu</span> Menu
            </button>
        </div>

        <nav id="mobile-menu" class="hidden border-t border-line md:hidden" aria-label="Navigasi seluler">
            <div class="container-page flex flex-col gap-1 py-3">
                <a href="{{ route('home') }}" class="nav-link">Beranda</a>
                <a href="{{ route('games.index') }}" class="nav-link">Katalog</a>
                <a href="{{ route('pages.how-to') }}" class="nav-link">Cara Top-Up</a>
                <a href="{{ route('tracking') }}" class="nav-link">Cek Pesanan</a>
                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="nav-link">Panel Admin</a>
                    @endif
                    <a href="{{ route('dashboard') }}" class="nav-link">Akun Saya</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf <button type="submit" class="nav-link w-full text-left">Keluar</button></form>
                @else
                    <a href="{{ route('login') }}" class="nav-link">Masuk</a>
                    <a href="{{ route('register') }}" class="nav-link">Daftar</a>
                @endauth
            </div>
        </nav>
    </header>

    <main id="main" class="flex-1">
        <div class="container-page pt-6">
            <x-flash />
        </div>
        @yield('content')
    </main>

    <footer class="mt-16 border-t border-line bg-surface">
        <div class="container-page grid gap-8 py-10 md:grid-cols-3">
            <div>
                <p class="text-lg font-bold text-white">{{ setting('store_name', 'Katniss Store') }}</p>
                <p class="mt-2 text-sm text-slate-400">{{ setting('tagline') }}</p>
                @if (setting('footer_note'))
                    <p class="mt-3 text-xs text-slate-500">{{ setting('footer_note') }}</p>
                @endif
            </div>
            <nav aria-label="Tautan footer">
                <p class="text-sm font-semibold text-white">Bantuan</p>
                <ul class="mt-3 space-y-2 text-sm text-slate-400">
                    <li><a class="hover:text-white" href="{{ route('pages.how-to') }}">Cara top-up</a></li>
                    <li><a class="hover:text-white" href="{{ route('pages.faq') }}">FAQ</a></li>
                    <li><a class="hover:text-white" href="{{ route('tracking') }}">Cek pesanan</a></li>
                    <li><a class="hover:text-white" href="{{ route('pages.contact') }}">Kontak</a></li>
                </ul>
            </nav>
            <nav aria-label="Tautan legal">
                <p class="text-sm font-semibold text-white">Legal</p>
                <ul class="mt-3 space-y-2 text-sm text-slate-400">
                    <li><a class="hover:text-white" href="{{ route('pages.terms') }}">Syarat layanan</a></li>
                    <li><a class="hover:text-white" href="{{ route('pages.privacy') }}">Kebijakan privasi</a></li>
                </ul>
            </nav>
        </div>
        <div class="border-t border-line py-4 text-center text-xs text-slate-500">
            © {{ now()->year }} {{ setting('store_name', 'Katniss Store') }}. Nama game dan merek adalah milik masing-masing pemegang hak.
        </div>
    </footer>
</body>
</html>
