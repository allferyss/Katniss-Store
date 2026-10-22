@extends('layouts.app')
@section('title', 'Kontak')

@section('content')
<section class="container-page max-w-2xl py-8">
    <h1 class="text-3xl">Kontak</h1>
    <div class="card mt-6 space-y-3 text-sm">
        @php $wa = preg_replace('/\D+/', '', (string) setting('contact_whatsapp')); @endphp
        @if (setting('contact_email'))<p><span class="text-slate-400">Email:</span> <a class="text-brand-400 hover:text-brand-300" href="mailto:{{ setting('contact_email') }}">{{ setting('contact_email') }}</a></p>@endif
        @if ($wa)<p><span class="text-slate-400">WhatsApp:</span> <a class="text-brand-400 hover:text-brand-300" href="https://wa.me/{{ $wa }}" rel="noopener" target="_blank">{{ setting('contact_whatsapp') }}</a></p>@endif
        @if (setting('contact_address'))<p><span class="text-slate-400">Alamat:</span> {{ setting('contact_address') }}</p>@endif
        @if (! setting('contact_email') && ! $wa && ! setting('contact_address'))
            <p class="text-slate-400">Informasi kontak belum diatur. Admin dapat mengisinya di Panel Admin → Pengaturan.</p>
        @endif
    </div>
</section>
@endsection
