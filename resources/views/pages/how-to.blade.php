@extends('layouts.app')
@section('title', 'Cara Top-Up')

@section('content')
<section class="container-page max-w-3xl py-8">
    <h1 class="text-3xl">Cara Top-Up</h1>
    <ol class="mt-6 space-y-4">
        @foreach ([
            ['Daftar / masuk akun', 'Pembuatan pesanan membutuhkan akun agar riwayat transaksi Anda tersimpan dan terlindungi.'],
            ['Pilih game dan nominal', 'Buka katalog, pilih game, lalu pilih nominal yang diinginkan.'],
            ['Isi Player ID', 'Masukkan Player ID (dan Server ID bila diminta). Petunjuk mencari ID tersedia di halaman tiap game. Jangan pernah memberikan password akun game.'],
            ['Periksa ringkasan', 'Pastikan game, nominal, dan ID sudah benar. Anda dapat memasukkan kode promo bila punya.'],
            ['Pilih metode pembayaran', 'Buat pesanan, lalu selesaikan pembayaran sesuai metode yang dipilih.'],
            ['Pantau status', 'Status pesanan dan pembayaran terlihat di riwayat pesanan, atau lewat menu Cek Pesanan dengan nomor pesanan.'],
        ] as $i => [$t, $d])
            <li class="card flex gap-4">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-600/20 text-sm font-bold text-brand-300">{{ $i + 1 }}</span>
                <div><h2 class="text-base">{{ $t }}</h2><p class="mt-1 text-sm text-slate-400">{{ $d }}</p></div>
            </li>
        @endforeach
    </ol>
    @if (is_simulation())
        <p class="mt-6 rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">Situs ini berjalan dalam <strong>mode simulasi/demo</strong>: pembayaran dan top-up tidak nyata.</p>
    @endif
</section>
@endsection
