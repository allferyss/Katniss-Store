<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentSimulationController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Publik (guest)
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/games', [GameController::class, 'index'])->name('games.index');
Route::get('/games/{game:slug}', [GameController::class, 'show'])->name('games.show');
Route::get('/cek-pesanan', [TrackingController::class, 'index'])->middleware('throttle:tracking')->name('tracking');

Route::view('/cara-top-up', 'pages.how-to')->name('pages.how-to');
Route::view('/faq', 'pages.faq')->name('pages.faq');
Route::view('/kebijakan-privasi', 'pages.privacy')->name('pages.privacy');
Route::view('/syarat-layanan', 'pages.terms')->name('pages.terms');
Route::view('/kontak', 'pages.contact')->name('pages.contact');

Route::get('/health', HealthController::class)->name('health');

// Webhook pembayaran: diverifikasi dengan signature HMAC (bukan CSRF).
Route::post('/webhooks/payment', PaymentWebhookController::class)->middleware('throttle:webhook')->name('webhooks.payment');

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [LoginController::class, 'create'])->name('login');
    Route::post('/masuk', [LoginController::class, 'store'])->middleware('throttle:login');

    Route::get('/daftar', [RegisterController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'store'])->middleware('throttle:register');

    Route::get('/lupa-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/lupa-password', [ForgotPasswordController::class, 'store'])->middleware('throttle:password-reset')->name('password.email');
    Route::get('/atur-ulang-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/atur-ulang-password', [ResetPasswordController::class, 'store'])->middleware('throttle:password-reset')->name('password.update');
});

Route::post('/keluar', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Customer (harus login)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:checkout')->name('checkout.store');

    Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/pesanan/{order}/simulasi-pembayaran', PaymentSimulationController::class)->middleware('throttle:checkout')->name('orders.simulate');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

/*
|--------------------------------------------------------------------------
| Admin (auth + role admin, dicek di server oleh middleware 'admin')
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::resource('games', Admin\GameController::class)->except('show');
    Route::resource('products', Admin\ProductController::class)->except('show');

    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order:id}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order:id}', [Admin\OrderController::class, 'update'])->name('orders.update');
    Route::post('/orders/{order:id}/topup', [Admin\OrderController::class, 'topup'])->name('orders.topup');

    Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}', [Admin\UserController::class, 'update'])->name('users.update');

    Route::resource('promotions', Admin\PromotionController::class)->except('show');

    Route::get('/reports', [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [Admin\ReportController::class, 'export'])->name('reports.export');

    Route::get('/settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [Admin\SettingController::class, 'update'])->name('settings.update');

    Route::get('/activity-logs', [Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
});
