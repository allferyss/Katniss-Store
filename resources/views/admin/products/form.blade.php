@extends('layouts.admin')
@php $editing = $product->exists; @endphp
@section('title', $editing ? 'Edit Produk' : 'Tambah Produk')
@section('heading', $editing ? 'Edit Produk' : 'Tambah Produk')

@section('content')
<form method="POST" action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}" class="card max-w-2xl space-y-4">
    @csrf @if ($editing) @method('PUT') @endif
    <div>
        <label for="game_id" class="label">Game <span class="text-red-400" aria-hidden="true">*</span></label>
        <select id="game_id" name="game_id" class="input" required>
            <option value="">— Pilih game —</option>
            @foreach ($games as $g)<option value="{{ $g->id }}" @selected((int) old('game_id', $product->game_id) === $g->id)>{{ $g->name }}</option>@endforeach
        </select>
        @error('game_id')<p class="field-error">{{ $message }}</p>@enderror
    </div>
    <x-field name="name" label="Nama produk / nominal" :value="$product->name" required maxlength="120" placeholder="Contoh: 100 Diamond" />
    <x-field name="description" label="Keterangan (opsional)" :value="$product->description" maxlength="255" />
    <div class="grid gap-4 sm:grid-cols-2">
        <x-field name="price" label="Harga jual (Rupiah)" type="number" :value="$product->price" required min="1000" step="1" hint="Bilangan bulat, tanpa titik." />
        <x-field name="cost_price" label="Harga modal (opsional)" type="number" :value="$product->cost_price" min="0" step="1" />
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <x-field name="sku" label="SKU supplier (opsional)" :value="$product->sku" maxlength="64" />
        <x-field name="sort_order" label="Urutan tampil" type="number" :value="$product->sort_order" min="0" />
    </div>
    @if ($editing)<p class="hint">Perubahan harga hanya berlaku untuk pesanan baru. Pesanan lama memakai harga saat transaksi.</p>@endif
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" class="checkbox" @checked(old('is_active', $product->is_active))> Aktif (bisa dipesan)</label>
    <div class="flex gap-3"><button class="btn-primary" type="submit">Simpan</button><a href="{{ route('admin.products.index') }}" class="btn-secondary">Batal</a></div>
</form>
@endsection
