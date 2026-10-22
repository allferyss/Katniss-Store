@extends('layouts.app')
@section('title', 'Atur Ulang Password')

@section('content')
<section class="container-page max-w-md py-10">
    <h1 class="text-3xl">Atur Ulang Password</h1>
    <form method="POST" action="{{ route('password.update') }}" class="card mt-6 space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-field name="email" label="Email" type="email" :value="$email" required autocomplete="username" />
        <x-field name="password" label="Password baru" type="password" required autocomplete="new-password" hint="Minimal 8 karakter, mengandung huruf dan angka." />
        <x-field name="password_confirmation" label="Ulangi password baru" type="password" required autocomplete="new-password" />
        <button type="submit" class="btn-primary w-full">Simpan password</button>
    </form>
</section>
@endsection
