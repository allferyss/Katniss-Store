@extends('layouts.app')
@section('title', 'Profil')

@section('content')
<section class="container-page max-w-2xl py-8">
    <h1 class="text-3xl">Profil Akun</h1>

    <form method="POST" action="{{ route('profile.update') }}" class="card mt-6 space-y-4">
        @csrf @method('PUT')
        <h2 class="text-lg">Data diri</h2>
        <x-field name="name" label="Nama" :value="$user->name" required autocomplete="name" />
        <x-field name="email" label="Email" type="email" :value="$user->email" required autocomplete="email" />
        <x-field name="phone" label="Nomor HP (opsional)" :value="$user->phone" autocomplete="tel" />
        <button type="submit" class="btn-primary">Simpan</button>
    </form>

    <form method="POST" action="{{ route('profile.password') }}" class="card mt-6 space-y-4">
        @csrf @method('PUT')
        <h2 class="text-lg">Ganti password</h2>
        <x-field name="current_password" label="Password saat ini" type="password" required autocomplete="current-password" bag="password" />
        <x-field name="password" label="Password baru" type="password" required autocomplete="new-password" hint="Minimal 8 karakter, mengandung huruf dan angka." bag="password" />
        <x-field name="password_confirmation" label="Ulangi password baru" type="password" required autocomplete="new-password" bag="password" />
        <button type="submit" class="btn-primary">Ganti password</button>
    </form>
</section>
@endsection
