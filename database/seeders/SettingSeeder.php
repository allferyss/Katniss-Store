<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (SettingService::DEFAULTS as $key => $value) {
            // firstOrCreate: tidak menimpa pengaturan yang sudah diubah admin.
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget('katniss.settings');
    }
}
