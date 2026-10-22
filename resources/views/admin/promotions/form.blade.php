@extends('layouts.admin')
@php $editing = $promotion->exists; @endphp
@section('title', $editing ? 'Edit Promo' : 'Buat Promo')
@section('heading', $editing ? 'Edit Promo' : 'Buat Promo')

@section('content')
<form method="POST" action="{{ $editing ? route('admin.promotions.update', $promotion) : route('admin.promotions.store') }}" class="card max-w-2xl space-y-4">
    @csrf @if ($editing) @method('PUT') @endif
    <div class="grid gap-4 sm:grid-cols-2">
        <x-field name="code" label="Kode promo" :value="$promotion->code" required maxlength="32" class="uppercase" hint="Huruf besar, angka, - dan _." />
        <x-field name="name" label="Nama promo" :value="$promotion->name" required maxlength="120" />
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="type" class="label">Tipe</label>
            <select id="type" name="type" class="input">
                <option value="percent" @selected(old('type', $promotion->type) === 'percent')>Persen (%)</option>
                <option value="fixed" @selected(old('type', $promotion->type) === 'fixed')>Potongan tetap (Rp)</option>
            </select>
        </div>
        <x-field name="value" label="Nilai" type="number" :value="$promotion->value" required min="1" hint="Persen maks 90; potongan tetap dalam Rupiah." />
    </div>
    <div class="grid gap-4 sm:grid-cols-3">
        <x-field name="min_purchase" label="Minimal pembelian (Rp)" type="number" :value="$promotion->min_purchase" min="0" />
        <x-field name="max_discount" label="Maks. potongan (Rp)" type="number" :value="$promotion->max_discount" min="1" hint="Opsional." />
        <x-field name="usage_limit" label="Kuota pemakaian" type="number" :value="$promotion->usage_limit" min="1" hint="Kosong = tanpa batas." />
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <x-field name="starts_at" label="Mulai berlaku" type="datetime-local" :value="$promotion->starts_at?->format('Y-m-d\TH:i')" />
        <x-field name="ends_at" label="Berakhir" type="datetime-local" :value="$promotion->ends_at?->format('Y-m-d\TH:i')" />
    </div>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" class="checkbox" @checked(old('is_active', $promotion->is_active))> Aktif</label>
    <div class="flex gap-3"><button class="btn-primary" type="submit">Simpan</button><a href="{{ route('admin.promotions.index') }}" class="btn-secondary">Batal</a></div>
</form>
@endsection
