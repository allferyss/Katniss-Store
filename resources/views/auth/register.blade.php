@extends('layouts.app')
@section('title', 'Daftar')

@section('content')
<section class="container-page max-w-md py-10">
    <h1 class="text-3xl">Buat Akun</h1>
    <form method="POST" action="{{ route('register') }}" class="card mt-6 space-y-4">
        @csrf
        <x-field name="name" label="Nama" required autocomplete="name" />
        <x-field name="email" label="Email" type="email" required autocomplete="email" />
        <x-field name="phone" label="Nomor HP (opsional)" autocomplete="tel" />
        <x-field name="password" label="Password" type="password" required autocomplete="new-password" hint="Minimal 8 karakter, mengandung huruf dan angka." />
        <x-field name="password_confirmation" label="Ulangi password" type="password" required autocomplete="new-password" />
        <button type="submit" class="btn-primary w-full">Daftar</button>
    </form>
    <p class="mt-4 text-center text-sm text-slate-400">Sudah punya akun? <a href="{{ route('login') }}" class="text-brand-400 hover:text-brand-300">Masuk</a></p>
</section>
@endsection
