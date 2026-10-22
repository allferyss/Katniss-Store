# Integrasi Payment Gateway & Supplier Top-up

Saat ini aplikasi hanya memiliki adapter **simulasi**. Dokumen ini menjelaskan cara menambahkan integrasi nyata tanpa mengubah alur pesanan. **Jangan** menulis kredensial asli ke repository; simpan di `.env` / secret store.

## 1. Kontrak

- `App\Contracts\PaymentGateway` – `createPayment()`, `verifyWebhookSignature()`, `name()`, `isSimulation()`.
- `App\Contracts\TopUpProvider` – `topUp(Order)` → `TopUpResult`.

Pemilihan implementasi ada di `App\Providers\AppServiceProvider` (`match` pada `PAYMENT_DRIVER` / `TOPUP_DRIVER`).

## 2. Menambah gateway pembayaran

1. Buat kelas, mis. `app/Services/Payments/ContohGateway.php` yang meng-implement `PaymentGateway`:
   - `createPayment()` memanggil API penyedia (mode **sandbox** dulu) dan mengembalikan `PaymentIntent(reference, driver, instructions)`. `reference` harus unik dan dipakai penyedia pada webhook.
   - `verifyWebhookSignature()` memverifikasi tanda tangan **sesuai dokumentasi penyedia** memakai body mentah. (Implementasi simulasi memakai HMAC-SHA256 pada header `X-Signature`.)
   - `isSimulation()` mengembalikan `false`.
2. Daftarkan di `AppServiceProvider`: `'contoh' => new ContohGateway(...)`.
3. `.env`: `PAYMENT_DRIVER=contoh`, `PAYMENT_WEBHOOK_SECRET=...` (+ kunci API penyedia, tambahkan ke `config/katniss.php` atau `config/services.php` lewat `env()`).
4. Saat driver bukan `fake`, route simulasi pembayaran otomatis menjadi **404**; label "MODE SIMULASI" otomatis hilang.

## 3. Webhook

Endpoint: `POST /webhooks/payment` (`PaymentWebhookController`). Perilaku saat ini:

1. Verifikasi signature dari **body mentah** → 401 bila salah / secret kosong.
2. Validasi payload `{event_id, reference, status: paid|failed, amount?}`.
3. Cek `reference` ada dan `amount` sama dengan nominal pembayaran.
4. Catat `event_id` di `webhook_events` (unik) → event ulang dibalas `duplicate: true` tanpa diproses lagi.
5. `markPaymentPaid` / `failPayment` bersifat idempoten dan memakai transaksi + penguncian baris.

Bila format webhook penyedia berbeda, ubah `PaymentWebhookController` (parsing payload) tetapi pertahankan urutan: verifikasi → validasi → anti-duplikasi → ubah status lewat `OrderService`.

Uji dengan sandbox penyedia dan, secara lokal, dengan perintah:
```bash
BODY='{"event_id":"evt-1","reference":"SIM-XXXX","status":"paid","amount":10000}'
SIG=$(printf '%s' "$BODY" | openssl dgst -sha256 -hmac "$PAYMENT_WEBHOOK_SECRET" | sed 's/^.* //')
curl -X POST http://localhost:8000/webhooks/payment -H "Content-Type: application/json" -H "X-Signature: $SIG" -d "$BODY"
```

## 4. Menambah supplier top-up

1. Buat kelas yang meng-implement `TopUpProvider`; `topUp()` memanggil API supplier memakai `$order->items->first()->product->sku`/`$order->player_id`/`$order->server_id` dan mengembalikan `TopUpResult(success, reference, message)`.
2. Gunakan **ID transaksi unik per pesanan** (mis. `order_number`) sebagai idempotency key di API supplier agar request ulang tidak top-up dua kali.
3. Daftarkan di `AppServiceProvider`, set `TOPUP_DRIVER`.
4. `TOPUP_AUTO_PROCESS=true` bila ingin otomatis setelah lunas; bila tidak, admin menekan "Jalankan top-up".
5. Isi kolom `products.sku` dengan kode produk supplier (tersedia di form produk admin).

## 5. Rekonsiliasi & hal yang belum ada

Belum tersedia: rekonsiliasi otomatis dengan laporan penyedia, polling status untuk webhook yang hilang, refund via API, dan retry top-up terjadwal. Rencanakan minimal: laporan harian pembayaran `pending` yang lebih tua dari N menit untuk dicek manual/otomatis ke API penyedia.

## 6. Checklist sebelum go-live

- [ ] Uji lengkap di sandbox (sukses, gagal, kedaluwarsa, webhook ganda, webhook palsu)
- [ ] Secret & API key hanya di environment
- [ ] Alamat webhook memakai HTTPS & dibatasi (allow-list IP penyedia bila ada)
- [ ] Kebijakan refund & kontak CS ditulis di Syarat Layanan
- [ ] Pantau log kegagalan webhook/top-up
