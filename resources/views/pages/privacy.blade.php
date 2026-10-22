@extends('layouts.app')
@section('title', 'Kebijakan Privasi')

@section('content')
<section class="container-page max-w-3xl space-y-4 py-8 text-sm leading-7 text-slate-300">
    <h1 class="text-3xl">Kebijakan Privasi</h1>
    <p class="rounded-lg border border-line bg-surface px-4 py-3 text-xs text-slate-400">Dokumen contoh. Pemilik toko wajib meninjau dan menyesuaikannya dengan praktik sebenarnya sebelum situs dipakai untuk transaksi nyata.</p>
    <h2 class="text-lg pt-2">Data yang kami kumpulkan</h2>
    <p>Nama, email, nomor HP (opsional), Player ID/Server ID untuk pesanan, serta riwayat pesanan. Kami tidak meminta atau menyimpan password akun game maupun data kartu pembayaran.</p>
    <h2 class="text-lg pt-2">Penggunaan data</h2>
    <p>Data dipakai untuk memproses pesanan, menampilkan riwayat transaksi, mengirim email akun (mis. atur ulang password), dan menjaga keamanan layanan.</p>
    <h2 class="text-lg pt-2">Penyimpanan dan keamanan</h2>
    <p>Password disimpan dalam bentuk hash. Kami menerapkan langkah keamanan yang wajar, namun tidak ada sistem yang sepenuhnya bebas risiko.</p>
    <h2 class="text-lg pt-2">Hak Anda</h2>
    <p>Anda dapat memperbarui data profil di halaman akun. Untuk permintaan lain terkait data, hubungi kami lewat halaman <a class="text-brand-400 hover:text-brand-300" href="{{ route('pages.contact') }}">Kontak</a>.</p>
</section>
@endsection
