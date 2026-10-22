@if (session('success'))
    <div role="status" class="mb-4 flex items-start justify-between gap-3 rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
        <span>{{ session('success') }}</span>
        <button type="button" data-dismiss class="shrink-0 text-emerald-300 hover:text-white" aria-label="Tutup pesan">✕</button>
    </div>
@endif
@if (session('error'))
    <div role="alert" class="mb-4 flex items-start justify-between gap-3 rounded-lg border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-200">
        <span>{{ session('error') }}</span>
        <button type="button" data-dismiss class="shrink-0 text-red-300 hover:text-white" aria-label="Tutup pesan">✕</button>
    </div>
@endif
@if ($errors->any() && ! $errors->hasBag('password') && ! isset($hideErrors))
    <div role="alert" class="mb-4 rounded-lg border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-200">
        <p class="font-semibold">Periksa kembali isian Anda:</p>
        <ul class="mt-1 list-inside list-disc">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
