# Arsitektur Singkat

Monolit Laravel klasik (MVC) tanpa microservices: mudah dipahami, dijalankan, dan dikontainerisasi.

## Lapisan

| Lapisan | Lokasi | Tanggung jawab |
|---|---|---|
| Routing | `routes/web.php` | Pemetaan URL; group `auth` dan `auth`+`admin` |
| Controller | `app/Http/Controllers` | Tipis: validasi (Form Request) → panggil service → respons |
| Validasi | `app/Http/Requests` | Aturan input; mencegah mass assignment (hanya `validated()/safe()`) |
| Otorisasi | `EnsureUserIsAdmin`, `OrderPolicy` | Dicek di server, bukan di tampilan |
| Logika bisnis | `app/Services` | `OrderService` (pesanan, pembayaran, top-up, transisi status), `PromotionService`, `SettingService`, `ActivityLogger` |
| Integrasi | `app/Contracts` + `Services/Payments|TopUp` | Adapter gateway & supplier (saat ini simulasi) |
| Data | `app/Models`, `database/migrations` | Eloquent + MySQL |
| Tampilan | `resources/views` | Blade + Tailwind |

## Model data

```
users 1─* orders 1─* order_items (snapshot: game_name, product_name, unit_price)
                │  └* payments (reference unik, status, payload non-sensitif)
                └─? promotions
games 1─* products            webhook_events (event_id unik)
settings (key/value)          activity_logs (aksi admin)
```
`order_items.game_id/product_id` bernilai `NULL` bila game/produk dihapus; riwayat tetap utuh karena memakai snapshot.

## Status

Pesanan: `pending → processing → completed`, serta `failed`, `cancelled`.
Pembayaran: `unpaid`, `pending`, `paid`, `failed`, `refunded`.

Aturan transisi ada di enum (`OrderStatus::transitions()`, `PaymentStatus::transitions()`) dan ditegakkan di `OrderService::updateStatuses()`. Aturan silang: Diproses/Selesai butuh pembayaran *Dibayar*; pesanan yang sudah dibayar tidak dapat dibatalkan sebelum di-refund; status akhir (Selesai/Dibatalkan) tidak dapat diubah.

## Alur pesanan

1. `GET /checkout` → ringkasan; harga dihitung dari DB; promo hanya pratinjau.
2. `POST /checkout` → `OrderService::createOrder()` dalam satu transaksi: kunci baris produk, validasi aktif, hitung harga & promo (`lockForUpdate` pada promo), buat `orders` + `order_items` + `payments(pending)`. `idempotency_key` unik mencegah pesanan ganda.
3. Pembayaran lunas (webhook terverifikasi, atau simulasi bila `PAYMENT_DRIVER=fake`) → `markPaymentPaid()` idempoten → order `processing`.
4. Top-up (`ProcessTopUpJob` → `OrderService::processTopUp()`): hanya untuk order `processing`+`paid` tanpa `topup_reference`; sukses → `completed`, gagal → `failed`.
5. Order belum dibayar melewati `ORDER_EXPIRE_HOURS` dibatalkan oleh scheduler dan kuota promo dikembalikan.

## Keamanan (ringkas)

CSRF (kecuali webhook yang memakai HMAC), validasi server-side, escaping Blade, Eloquent/parameter binding, rate limiting (`login`, `register`, `password-reset`, `checkout`, `tracking`, `webhook`), hashing password bawaan Laravel, cookie sesi `http_only` + `same_site=lax` (+ `secure` di HTTPS), upload tervalidasi (MIME, ukuran, nama acak, tanpa SVG), ekspor CSV aman dari formula injection, `APP_DEBUG=false` di production, tidak ada secret di repository.
