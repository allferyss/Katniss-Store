@extends('layouts.admin')
@php $editing = $game->exists; @endphp
@section('title', $editing ? 'Edit Game' : 'Tambah Game')
@section('heading', $editing ? 'Edit Game' : 'Tambah Game')

@section('content')
<form method="POST" action="{{ $editing ? route('admin.games.update', $game) : route('admin.games.store') }}" enctype="multipart/form-data" class="card max-w-2xl space-y-4">
    @csrf @if ($editing) @method('PUT') @endif
    <x-field name="name" label="Nama game" :value="$game->name" required maxlength="120" />
    <x-field name="slug" label="Slug" :value="$game->slug" maxlength="140" hint="Kosongkan agar dibuat otomatis dari nama." />
    <x-field name="publisher" label="Publisher" :value="$game->publisher" maxlength="120" />
    <div>
        <label for="description" class="label">Deskripsi</label>
        <textarea id="description" name="description" rows="4" maxlength="3000" class="input">{{ old('description', $game->description) }}</textarea>
        @error('description')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="id_hint" class="label">Petunjuk menemukan Player ID / Server ID</label>
        <textarea id="id_hint" name="id_hint" rows="3" maxlength="1000" class="input">{{ old('id_hint', $game->id_hint) }}</textarea>
        @error('id_hint')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="image" class="label">Gambar (JPG/PNG/WebP, maks {{ config('katniss.uploads.max_kb') / 1024 }} MB)</label>
        <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" class="input">
        @error('image')<p class="field-error">{{ $message }}</p>@enderror
        @if ($editing && $game->image_path)<img src="{{ $game->image_url }}" alt="Gambar saat ini" class="mt-3 h-24 rounded-lg border border-line">@endif
    </div>
    <x-field name="sort_order" label="Urutan tampil" type="number" :value="$game->sort_order" min="0" />
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="requires_server_id" value="1" class="checkbox" @checked(old('requires_server_id', $game->requires_server_id))> Game ini membutuhkan Server ID</label>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" class="checkbox" @checked(old('is_active', $game->is_active))> Aktif (tampil di katalog)</label>
    <div class="flex gap-3"><button class="btn-primary" type="submit">Simpan</button><a href="{{ route('admin.games.index') }}" class="btn-secondary">Batal</a></div>
</form>
@endsection
