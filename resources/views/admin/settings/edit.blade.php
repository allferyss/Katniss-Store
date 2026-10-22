@extends('layouts.admin')
@section('title', 'Pengaturan')
@section('heading', 'Pengaturan Toko')

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="card max-w-2xl space-y-4">
    @csrf @method('PUT')
    <x-field name="store_name" label="Nama toko" :value="$values['store_name']" required maxlength="60" />
    <x-field name="tagline" label="Tagline" :value="$values['tagline']" maxlength="160" />
    <div class="grid gap-4 sm:grid-cols-2">
        <x-field name="contact_email" label="Email kontak" type="email" :value="$values['contact_email']" maxlength="120" />
        <x-field name="contact_whatsapp" label="WhatsApp" :value="$values['contact_whatsapp']" maxlength="20" hint="Contoh: 6281234567890" />
    </div>
    <x-field name="contact_address" label="Alamat" :value="$values['contact_address']" maxlength="255" />
    <x-field name="order_notice" label="Catatan di halaman checkout" :value="$values['order_notice']" maxlength="300" />
    <x-field name="footer_note" label="Catatan footer" :value="$values['footer_note']" maxlength="300" />
    <div>
        <label for="logo" class="label">Logo (JPG/PNG/WebP)</label>
        <input id="logo" type="file" name="logo" accept="image/jpeg,image/png,image/webp" class="input">
        @error('logo')<p class="field-error">{{ $message }}</p>@enderror
        @if ($values['logo_path'])
            <div class="mt-3 flex items-center gap-4">
                <img src="{{ upload_url($values['logo_path']) }}" alt="Logo saat ini" class="h-14 w-14 rounded-lg border border-line object-cover">
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remove_logo" value="1" class="checkbox"> Hapus logo</label>
            </div>
        @endif
    </div>
    <button class="btn-primary" type="submit">Simpan pengaturan</button>
</form>
@endsection
