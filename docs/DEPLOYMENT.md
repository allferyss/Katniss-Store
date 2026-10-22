# Panduan Deployment (netral vendor)

Panduan ini melengkapi README (bagian 10–15). Nama vendor sengaja tidak disebut: istilah yang dipakai adalah "VPS", "managed database", "object storage", dan "container service".

## 1. Peta komponen

```
Pengguna ──HTTPS──> [Reverse proxy / Nginx] ──> [PHP-FPM / container aplikasi] ──> [MySQL]
                                                      │
                                                      ├──> [Object storage S3-compatible]  (upload gambar/logo)
                                                      ├──> [Redis]  (opsional: cache/queue)
                                                      └──> [SMTP]   (email reset password)
```

Aplikasi bersifat **stateless** bila: sesi di database/Redis (`SESSION_DRIVER=database`), upload di object storage (`UPLOAD_DISK=s3`), dan log ke stderr. Dengan begitu beberapa instance aplikasi dapat berjalan di belakang load balancer.

## 2. Variabel environment

Semua konfigurasi ada di `.env.example`. Set variabel ini lewat panel/secret store penyedia, **bukan** di Git.

| Variabel | Keterangan |
|---|---|
| `APP_KEY` | Wajib, unik per lingkungan (`php artisan key:generate --show`). Instance yang berbagi sesi harus memakai kunci yang sama. |
| `APP_ENV`, `APP_DEBUG`, `APP_URL` | production / `false` / URL HTTPS publik |
| `FORCE_HTTPS`, `SESSION_SECURE_COOKIE` | `true` bila HTTPS |
| `DB_*`, `MYSQL_ATTR_SSL_CA` | koneksi MySQL (lihat bagian 3) |
| `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION` | `database` atau `redis` |
| `UPLOAD_DISK` + `AWS_*` | `public` (lokal) atau `s3` (S3-compatible) |
| `MAIL_*` | SMTP |
| `PAYMENT_DRIVER`, `PAYMENT_WEBHOOK_SECRET`, `TOPUP_DRIVER` | integrasi pembayaran/supplier |
| `LOG_CHANNEL` | `stderr` untuk container; `single`/`daily` untuk VPS |

## 3. Menghubungkan ke managed MySQL

1. Buat instance MySQL 8 di penyedia pilihan Anda, pilih wilayah yang **sama/dekat** dengan server aplikasi (latensi rendah, trafik jaringan privat lebih murah/aman).
2. Buat database `katniss_store` (utf8mb4) dan **user khusus aplikasi** dengan hak hanya pada database itu.
3. Izinkan koneksi hanya dari server aplikasi (allow-list IP, security group, atau jaringan privat/VPC). Jangan membuka database ke `0.0.0.0/0`.
4. Bila SSL wajib, unduh sertifikat CA dari penyedia, simpan di server/image, dan isi `MYSQL_ATTR_SSL_CA`.
5. Isi `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
6. Uji dari server aplikasi: `php artisan migrate:status`, lalu `php artisan migrate --force`.
7. Aktifkan automated backup / point-in-time recovery di penyedia, dan tetap jadwalkan dump logis (README bagian 14).

Uji cepat tanpa aplikasi: `mysql -h HOST -P 3306 -u USER -p --ssl-ca=ca.crt DATABASE -e "SELECT 1"`.

## 4. Docker

`Dockerfile` bertingkat: (1) `composer install --no-dev`, (2) `npm run build`, (3) runtime `php:8.3-apache` dengan document root `public/`. `docker/entrypoint.sh` memeriksa `APP_KEY`, membuat `storage:link`, menjalankan migrasi bila `RUN_MIGRATIONS=true`, dan men-cache konfigurasi pada `APP_ENV=production`.

Perbedaan development vs production:

| | Development (lokal tanpa Docker) | Production (Docker/VPS) |
|---|---|---|
| `APP_ENV` / `APP_DEBUG` | `local` / `true` | `production` / `false` |
| Seeder | membuat data & akun demo | hanya `SettingSeeder`; admin lewat `katniss:create-admin` |
| Asset | `npm run dev` | `npm run build` (sudah di dalam image) |
| Cache konfigurasi | tidak | `config:cache`, `route:cache`, `view:cache` |
| Database | MySQL lokal | MySQL di container (volume) atau managed |

Catatan Docker Compose bawaan: port database **tidak** dipublikasikan; password diambil dari `.env` (tidak ada secret di file compose); volume `storage-public` menyimpan upload lokal. Untuk lebih dari satu instance aplikasi, ganti ke `UPLOAD_DISK=s3`.

Menjalankan perintah artisan di container: `docker compose exec app php artisan <perintah>`.

## 5. Container service / PaaS

- Build dari `Dockerfile` (atau buildpack PHP bila tersedia).
- *Release/pre-deploy command:* `php artisan migrate --force`.
- Health check HTTP: `GET /health` (200 bila DB terjangkau, 503 bila tidak; tidak membocorkan detail).
- Port container: 80.
- Set `LOG_CHANNEL=stderr`, `APP_ENV=production`, `FORCE_HTTPS=true`.
- Jalankan scheduler sebagai proses/cron terpisah: `php artisan schedule:work` (atau cron tiap menit `schedule:run`).
- Jalankan worker terpisah bila `QUEUE_CONNECTION != sync`: `php artisan queue:work --tries=1`.

## 6. Object storage (S3-compatible)

```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```
```env
UPLOAD_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=...
AWS_BUCKET=nama-bucket
AWS_ENDPOINT=https://endpoint-s3-compatible   # kosongkan untuk S3 asli
AWS_USE_PATH_STYLE_ENDPOINT=true              # umumnya true untuk MinIO/layanan sejenis
AWS_URL=https://url-publik-bucket             # URL dasar untuk menampilkan gambar
```
Bucket harus memungkinkan objek di prefix `games/` dan `branding/` dibaca publik (atau lewat CDN). Hak akses kunci API cukup `put/get/delete` pada bucket tersebut. Gambar yang sudah terunggah di disk lokal perlu disalin manual ke bucket bila Anda berpindah disk.

## 7. Queue & scheduler di VPS

- Scheduler: cron `* * * * * cd /var/www/katniss-store && php artisan schedule:run >> /dev/null 2>&1` (menjalankan `katniss:cancel-expired-orders` tiap jam).
- Queue worker: salin `docker/supervisor-queue.conf` ke `/etc/supervisor/conf.d/`, lalu `sudo supervisorctl reread && sudo supervisorctl update`. Setelah deploy: `php artisan queue:restart`.
- `ProcessTopUpJob` sengaja `tries = 1` agar tidak terjadi top-up ganda ke supplier.

## 8. Reverse proxy & IP klien

`bootstrap/app.php` memakai `trustProxies(at: '*')` agar skema HTTPS dan IP klien dibaca dari header proxy. Bila server aplikasi bisa diakses langsung dari internet, header dapat dipalsukan (memengaruhi rate limiting). Perbaiki dengan: (a) hanya menerima trafik dari proxy/load balancer (firewall), atau (b) mengganti `'*'` dengan daftar IP proxy Anda.

## 9. Zero-downtime sederhana (opsional)

Untuk VPS tunggal cukup `php artisan down` → update → `php artisan up`. Untuk meminimalkan downtime: deploy ke direktori rilis baru (`releases/AAAAMMDD-HHMM`), jalankan composer/build/migrasi, lalu ganti symlink `current` → rilis baru dan `systemctl reload php8.3-fpm`. Migrasi yang tidak kompatibel mundur harus dipecah menjadi dua rilis.

## 10. Keamanan operasional

- Update OS & PHP rutin; `composer audit` dan `npm audit` berkala.
- SSH hanya dengan key, nonaktifkan login root & password; pertimbangkan `fail2ban`.
- `.env` izin `600` dan milik user deploy.
- Rotasi `PAYMENT_WEBHOOK_SECRET` dan kredensial bila bocor. Jika `APP_KEY` diganti, semua sesi aktif menjadi tidak valid.
- Aplikasi tidak menyimpan password akun game maupun data kartu pembayaran.
