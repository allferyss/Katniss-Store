@extends('layouts.app')
@section('title', 'Masuk')

@section('content')
<section class="container-page max-w-md py-10">
    <h1 class="text-3xl">Masuk</h1>
    <form method="POST" action="{{ route('login') }}" class="card mt-6 space-y-4">
        @csrf
        <x-field name="email" label="Email" type="email" required autofocus autocomplete="username" />
        <x-field name="password" label="Password" type="password" required autocomplete="current-password" />
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-slate-300"><input type="checkbox" name="remember" value="1" class="checkbox"> Ingat saya</label>
            <a href="{{ route('password.request') }}" class="text-sm text-brand-400 hover:text-brand-300">Lupa password?</a>
        </div>
        <button type="submit" class="btn-primary w-full">Masuk</button>
    </form>
    <p class="mt-4 text-center text-sm text-slate-400">Belum punya akun? <a href="{{ route('register') }}" class="text-brand-400 hover:text-brand-300">Daftar</a></p>
</section>
@endsection
