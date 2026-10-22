<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingRequest;
use App\Services\ActivityLogger;
use App\Services\SettingService;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function edit(SettingService $settings)
    {
        return view('admin.settings.edit', ['values' => $settings->all()]);
    }

    public function update(SettingRequest $request, SettingService $settings, ActivityLogger $logger)
    {
        $values = $request->safe()->only([
            'store_name', 'tagline', 'contact_email', 'contact_whatsapp',
            'contact_address', 'order_notice', 'footer_note',
        ]);
        $values = array_map(fn ($v) => $v ?? '', $values);

        $disk = config('katniss.uploads.disk');
        $currentLogo = $settings->get('logo_path');

        if ($request->hasFile('logo')) {
            $values['logo_path'] = $request->file('logo')->store('branding', $disk);
            if ($currentLogo) {
                Storage::disk($disk)->delete($currentLogo);
            }
        } elseif ($request->boolean('remove_logo') && $currentLogo) {
            Storage::disk($disk)->delete($currentLogo);
            $values['logo_path'] = '';
        }

        $settings->setMany($values);
        $logger->log('settings.updated', 'Memperbarui pengaturan toko');

        return back()->with('success', 'Pengaturan toko disimpan.');
    }
}
