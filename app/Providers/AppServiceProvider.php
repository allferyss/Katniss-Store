<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Contracts\TopUpProvider;
use App\Services\Payments\FakePaymentGateway;
use App\Services\SettingService;
use App\Services\TopUp\FakeTopUpProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingService::class);

        // Pilih implementasi pembayaran / top-up lewat .env (PAYMENT_DRIVER, TOPUP_DRIVER).
        // Untuk integrasi nyata: buat kelas yang meng-implement kontrak, lalu tambahkan case di sini.
        $this->app->bind(PaymentGateway::class, fn () => match (config('katniss.payment.driver')) {
            'fake' => new FakePaymentGateway,
            default => throw new RuntimeException('PAYMENT_DRIVER "'.config('katniss.payment.driver').'" belum diimplementasikan. Lihat docs/INTEGRATION.md.'),
        });

        $this->app->bind(TopUpProvider::class, fn () => match (config('katniss.topup.driver')) {
            'fake' => new FakeTopUpProvider,
            default => throw new RuntimeException('TOPUP_DRIVER "'.config('katniss.topup.driver').'" belum diimplementasikan. Lihat docs/INTEGRATION.md.'),
        });
    }

    public function boot(): void
    {
        if (config('app.force_https')) {
            URL::forceHttps();
        }

        Paginator::defaultView('pagination.katniss');

        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        $this->configureRateLimiting();
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', fn (Request $r) => Limit::perMinute(5)->by(Str::lower((string) $r->input('email')).'|'.$r->ip()));
        RateLimiter::for('register', fn (Request $r) => Limit::perHour(10)->by($r->ip()));
        RateLimiter::for('password-reset', fn (Request $r) => Limit::perMinute(5)->by($r->ip()));
        RateLimiter::for('checkout', fn (Request $r) => Limit::perMinute(10)->by($r->user()?->id ?: $r->ip()));
        RateLimiter::for('tracking', fn (Request $r) => Limit::perMinute(20)->by($r->ip()));
        RateLimiter::for('webhook', fn (Request $r) => Limit::perMinute(120)->by($r->ip()));
    }
}
