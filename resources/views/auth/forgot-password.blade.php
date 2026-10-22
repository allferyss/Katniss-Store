@extends('layouts.app')
@section('title', 'Lupa Password')

@section('content')
<section class="container-page max-w-md py-10">
    <h1 class="text-3xl">Lupa Password</h1>
    <p class="mt-2 text-sm text-slate-400">Masukkan email akun Anda. Jika terdaftar, kami akan mengirim tautan untuk mengatur ulang password.</p>
    <form method="POST" action="{{ route('password.email') }}" class="card mt-6 space-y-4">
        @csrf
        <x-field name="email" label="Email" type="email" required autofocus autocomplete="email" />
        <button type="submit" class="btn-primary w-full">Kirim tautan</button>
    </form>
    <p class="mt-4 text-center text-sm"><a href="{{ route('login') }}" class="text-brand-400 hover:text-brand-300">← Kembali masuk</a></p>
</section>
@endsection
