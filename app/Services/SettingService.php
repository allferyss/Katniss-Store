<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;

/** Pengaturan toko sederhana (key-value) dengan cache. */
class SettingService
{
    private const CACHE_KEY = 'katniss.settings';

    /** Nilai bawaan bila belum ada di database. */
    public const DEFAULTS = [
        'store_name' => 'Katniss Store',
        'tagline' => 'Top-up game: pilih game, isi Player ID, pilih nominal.',
        'contact_email' => '',
        'contact_whatsapp' => '',
        'contact_address' => '',
        'order_notice' => 'Pastikan Player ID dan Server ID sudah benar. Kesalahan input bukan tanggung jawab toko.',
        'footer_note' => '',
        'logo_path' => '',
    ];

    /** @return array<string, string|null> */
    public function all(): array
    {
        try {
            $stored = Cache::remember(self::CACHE_KEY, 3600, fn () => Setting::pluck('value', 'key')->all());
        } catch (QueryException) {
            // Tabel belum ada (sebelum migrate): pakai nilai bawaan.
            $stored = [];
        }

        return array_merge(self::DEFAULTS, array_filter($stored, fn ($v) => $v !== null));
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $value = $this->all()[$key] ?? null;

        return ($value === null || $value === '') ? ($default ?? $value) : $value;
    }

    /** @param array<string, string|null> $values */
    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }
}
