<?php

use App\Services\SettingService;
use Illuminate\Support\Facades\Storage;

if (! function_exists('rupiah')) {
    /**
     * Format angka menjadi Rupiah, contoh: Rp 15.000.
     * Nilai uang disimpan sebagai bilangan bulat (Rupiah) di database.
     */
    function rupiah(int|float|string|null $amount): string
    {
        return 'Rp '.number_format((float) ($amount ?? 0), 0, ',', '.');
    }
}

if (! function_exists('setting')) {
    /** Ambil pengaturan toko (tabel settings, dengan cache). */
    function setting(string $key, ?string $default = null): ?string
    {
        return app(SettingService::class)->get($key, $default);
    }
}

if (! function_exists('upload_url')) {
    /** URL file upload dari disk Filesystem yang dikonfigurasi, atau gambar placeholder lokal. */
    function upload_url(?string $path, string $fallback = 'images/game-placeholder.svg'): string
    {
        if (blank($path)) {
            return asset($fallback);
        }

        return Storage::disk(config('katniss.uploads.disk'))->url($path);
    }
}

if (! function_exists('is_simulation')) {
    /** true bila pembayaran berjalan dalam mode SIMULASI / DEMO. */
    function is_simulation(): bool
    {
        return config('katniss.payment.driver') === 'fake';
    }
}
