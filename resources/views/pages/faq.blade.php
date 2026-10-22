@extends('layouts.app')
@section('title', 'FAQ')

@section('content')
<section class="container-page max-w-3xl py-8">
    <h1 class="text-3xl">Pertanyaan Umum</h1>
    <div class="mt-6 space-y-3">
        @foreach ([
            ['Apakah saya perlu membuat akun?', 'Ya. Akun dibutuhkan untuk membuat pesanan dan melihat riwayatnya.'],
            ['Apakah toko meminta password akun game saya?', 'Tidak. Kami hanya membutuhkan Player ID dan, bila relevan, Server ID. Jangan pernah membagikan password.'],
            ['Salah memasukkan Player ID, bagaimana?', 'Segera hubungi kontak toko dengan nomor pesanan Anda. Pesanan yang sudah diproses ke ID yang salah mungkin tidak dapat dibatalkan.'],
            ['Bagaimana cara melihat status pesanan?', 'Buka Riwayat Pesanan di akun Anda, atau gunakan menu Cek Pesanan dengan nomor pesanan.'],
            ['Apakah harga bisa berubah setelah pesanan dibuat?', 'Tidak. Harga yang tercatat pada pesanan tidak berubah walau harga produk diperbarui kemudian.'],
            ['Apakah transaksi di situs ini nyata?', is_simulation() ? 'Saat ini situs berjalan dalam mode simulasi/demo: tidak ada uang atau item game nyata yang diproses.' : 'Pembayaran diproses melalui penyedia pembayaran yang terhubung. Status mengikuti konfirmasi dari penyedia.'],
        ] as [$q, $a])
            <details class="card">
                <summary class="cursor-pointer font-medium text-white">{{ $q }}</summary>
                <p class="mt-2 text-sm text-slate-300">{{ $a }}</p>
            </details>
        @endforeach
    </div>
</section>
@endsection
