<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi khusus Katniss Store
|--------------------------------------------------------------------------
| Semua nilai bisa diubah lewat .env tanpa mengubah source code.
*/

return [
    // Total pesanan minimal setelah diskon (Rupiah). Mencegah pesanan bernilai nol.
    'min_order_total' => (int) env('MIN_ORDER_TOTAL', 1000),

    'payment' => [
        // 'fake' = simulasi untuk demo. Driver nyata harus diimplementasikan
        // lewat App\Contracts\PaymentGateway (lihat docs/INTEGRATION.md).
        'driver' => env('PAYMENT_DRIVER', 'fake'),

        // Rahasia HMAC untuk memverifikasi webhook (header X-Signature). Wajib diisi bila webhook dipakai.
        'webhook_secret' => env('PAYMENT_WEBHOOK_SECRET'),

        // Pesanan yang belum dibayar dibatalkan otomatis setelah N jam (butuh scheduler berjalan).
        'expire_hours' => (int) env('ORDER_EXPIRE_HOURS', 24),

        // Metode pembayaran yang tampil di checkout. Pada mode simulasi semuanya hanya simulasi.
        'methods' => [
            'qris' => ['label' => 'QRIS', 'hint' => 'Scan kode QR dengan aplikasi pembayaran apa pun.'],
            'bank_va' => ['label' => 'Virtual Account Bank', 'hint' => 'Transfer ke nomor virtual account.'],
            'ewallet' => ['label' => 'Dompet Digital', 'hint' => 'Bayar lewat aplikasi dompet digital.'],
        ],
    ],

    'topup' => [
        // 'fake' = simulasi. Driver nyata diimplementasikan lewat App\Contracts\TopUpProvider.
        'driver' => env('TOPUP_DRIVER', 'fake'),

        // true = setelah pembayaran lunas, top-up langsung diproses (mode demo).
        // false = admin menekan tombol proses top-up secara manual.
        'auto_process' => (bool) env('TOPUP_AUTO_PROCESS', false),
    ],

    'uploads' => [
        // Disk Laravel Filesystem untuk gambar/logo. Ganti ke 's3' agar file tidak hilang saat redeploy.
        'disk' => env('UPLOAD_DISK', 'public'),
        'max_kb' => (int) env('UPLOAD_MAX_KB', 2048),
    ],
];
