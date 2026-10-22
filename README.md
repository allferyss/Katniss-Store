# Katniss Store

Aplikasi web **top-up game** berbasis **Laravel + MySQL**, dibuat untuk proyek mata kuliah Cloud Computing. Pengunjung bisa melihat katalog game, memilih nominal, mengisi Player ID, membuat pesanan, dan memeriksa status transaksi. Admin mengelola katalog, harga, pesanan, pelanggan, promo, dan laporan.

> **PENTING – MODE SIMULASI.** Proyek ini **tidak terhubung** ke payment gateway maupun supplier top-up sungguhan. Pembayaran dan top-up berjalan lewat adapter simulasi (`FakePaymentGateway`, `FakeTopUpProvider`) dan diberi label **SIMULASI / DEMO** di antarmuka. Tidak ada uang atau item game nyata yang diproses. Cara menyambungkan layanan nyata ada di [`docs/INTEGRATION.md`](docs/INTEGRATION.md).

## Daftar isi

1. [Fitur](#1-fitur)
2. [Teknologi & persyaratan versi](#2-teknologi--persyaratan-versi)
3. [Yang harus Anda install di komputer](#3-yang-harus-anda-install-di-komputer)
4. [Setup lokal langkah demi langkah](#4-setup-lokal-langkah-demi-langkah)
5. [Menghubungkan database](#5-menghubungkan-database)
6. [Akun demo & membuat admin](#6-akun-demo--membuat-admin)
7. [Menjalankan test](#7-menjalankan-test)
8. [Struktur folder](#8-struktur-folder)
9. [Konfigurasi: mail, queue, cache, storage, mode demo](#9-konfigurasi-mail-queue-cache-storage-mode-demo)
10. [Deploy ke VPS Linux (langkah demi langkah)](#10-deploy-ke-vps-linux-langkah-demi-langkah)
11. [Deploy ke cloud / managed hosting / Docker](#11-deploy-ke-cloud--managed-hosting--docker)
12. [Domain, DNS, HTTPS, firewall](#12-domain-dns-https-firewall)
13. [Checklist production](#13-checklist-production)
14. [Backup & restore](#14-backup--restore)
15. [Monitoring, log, troubleshooting, update & rollback](#15-monitoring-log-troubleshooting-update--rollback)
16. [Biaya](#16-biaya)
17. [Known limitations](#17-known-limitations)

---

## 1. Fitur

**Publik / Guest:** beranda, katalog game (cari), detail game + petunjuk mencari Player ID/Server ID, daftar nominal, cek status pesanan lewat nomor pesanan, FAQ, cara top-up, kebijakan privasi, syarat layanan, kontak.

**Customer:** registrasi, login, lupa/reset password, dashboard, checkout (ringkasan harga + kode promo), riwayat & detail pesanan, profil & ganti password.

**Admin (`/admin`):** dashboard (angka dari database), CRUD game & produk (upload gambar), pengelolaan pesanan (cari, filter, paginasi, ubah status dengan validasi transisi, jalankan top-up simulasi), pengelolaan pengguna & role, promo, laporan transaksi + ekspor CSV, pengaturan toko (nama, logo, kontak), log aktivitas admin.

**Keamanan & keandalan:** CSRF, validasi server-side, otorisasi di server (middleware + Policy), rate limiting, harga selalu dihitung di server, snapshot harga/nama produk di pesanan, kunci idempotensi anti-pesanan ganda, transaksi database, webhook dengan verifikasi HMAC + anti-duplikasi, upload tervalidasi (nama acak, SVG ditolak).

## 2. Teknologi & persyaratan versi

| Komponen | Versi |
|---|---|
| PHP | **8.2 atau lebih baru** (disarankan 8.3) |
| Laravel | 12.x (`laravel/framework ^12.0`) |
| Database | **MySQL 8.0** (MariaDB 10.6+ umumnya juga bisa) |
| Composer | 2.x |
| Node.js | 20 LTS atau lebih baru (hanya untuk build CSS/JS) |
| Frontend | Blade, Tailwind CSS 3, Vite 6, JavaScript secukupnya |
| Test | PHPUnit 11 |

Ekstensi PHP yang dibutuhkan: `ctype, curl, dom, fileinfo, json, mbstring, openssl, pdo, pdo_mysql, tokenizer, xml`. Untuk menjalankan semua test juga butuh `pdo_sqlite` dan `gd`.

> **Catatan jujur:** proyek ini ditulis di lingkungan tanpa PHP/Composer/internet, sehingga **belum pernah dijalankan** (`composer install`, `migrate`, `php artisan test`, `npm run build` belum diuji). Kode ditulis hati-hati, tetapi kemungkinan ada kesalahan kecil yang baru muncul saat pertama dijalankan. Jika ada error, salin pesan lengkapnya (dan nama file/barisnya) untuk diperbaiki. Nomor versi paket akan diselesaikan Composer/npm saat `install` dan dikunci di `composer.lock` / `package-lock.json` yang akan terbentuk.

Pilihan desain: autentikasi memakai `Auth` bawaan Laravel (guard session, hashing, password broker) dengan controller sederhana, **bukan** Breeze/Livewire, karena paket tersebut harus di-install lewat Composer dan tidak bisa diuji di sini. Antarmuka memakai Blade + Tailwind + JS ringan.

## 3. Yang harus Anda install di komputer

Pilih **salah satu** jalur:

### Jalur A – Windows (paling mudah): Laragon
1. Install **[Laragon Full](https://laragon.org/download/)** (sudah berisi PHP, MySQL, Composer, Node.js, Git). Pastikan PHP ≥ 8.2 (ganti versi lewat menu Laragon → PHP bila perlu).
2. Jalankan Laragon → **Start All** (Apache/Nginx + MySQL aktif).
3. Install **[VS Code](https://code.visualstudio.com/)**.

### Jalur B – Windows: XAMPP
1. Install **XAMPP** dengan PHP ≥ 8.2, lalu **Composer** ([getcomposer.org](https://getcomposer.org/download/)), **Node.js LTS** ([nodejs.org](https://nodejs.org)), **Git** ([git-scm.com](https://git-scm.com)).
2. Di XAMPP Control Panel, Start **MySQL**. Aktifkan ekstensi `gd`, `pdo_mysql`, `pdo_sqlite`, `intl`, `zip` di `php.ini` (hapus tanda `;` di depan barisnya), lalu restart.

### Jalur C – macOS / Linux
Install PHP 8.3 + ekstensi di atas, MySQL 8, Composer, Node.js 20+, Git (mis. Homebrew: `brew install php mysql composer node git`; Ubuntu: lihat bagian VPS).

### Ekstensi VS Code yang berguna (opsional)
*PHP Intelephense*, *Laravel Blade Snippets*, *Laravel Extra Intellisense*, *Tailwind CSS IntelliSense*, *DotENV*.

Cek instalasi di terminal VS Code (`Ctrl + \``):

```bash
php -v          # harus 8.2 atau lebih
composer -V
node -v         # 20 atau lebih
npm -v
mysql --version
git --version
```

## 4. Setup lokal langkah demi langkah

Buka folder proyek di VS Code (`File → Open Folder`), buka terminal, lalu:

```bash
# 1) Pasang dependency PHP (membuat folder vendor/ dan composer.lock)
composer install

# 2) Pasang dependency front-end (membuat node_modules/)
npm install

# 3) Buat file konfigurasi
cp .env.example .env          # Windows CMD: copy .env.example .env

# 4) Buat kunci aplikasi (mengisi APP_KEY di .env)
php artisan key:generate
```

5. **Buat database kosong** dan atur `.env` – lihat [bagian 5](#5-menghubungkan-database).
6. Buat tabel + data awal:

```bash
php artisan migrate --seed     # membuat tabel, pengaturan toko, data contoh (hanya di APP_ENV=local)
php artisan storage:link       # agar gambar upload bisa tampil
```

7. Jalankan aplikasi. Ada dua cara:

```bash
# Cara 1 (development, hot reload): buka DUA terminal
php artisan serve              # terminal 1 -> http://localhost:8000
npm run dev                    # terminal 2 -> Vite

# Cara 2 (tanpa terminal kedua): build sekali lalu serve
npm run build
php artisan serve
```

Buka **http://localhost:8000**. Jika memakai Laragon, bisa juga lewat `http://katniss-store.test`.

> Urutan penting: `php artisan migrate` **harus** dijalankan sebelum membuka situs, karena sesi & cache memakai tabel database (`SESSION_DRIVER=database`).

## 5. Menghubungkan database

Aplikasi terhubung ke MySQL **hanya lewat file `.env`** (tanpa mengubah kode). Ada tiga skenario:

### 5a. Database lokal (Laragon/XAMPP/MySQL di komputer sendiri)

1. Buat database. Pilih salah satu:
   - **phpMyAdmin / HeidiSQL (Laragon)**: buat database `katniss_store` dengan collation `utf8mb4_unicode_ci`.
   - **Terminal MySQL**:
     ```sql
     CREATE DATABASE katniss_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
     ```
2. Isi `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=katniss_store
   DB_USERNAME=root
   DB_PASSWORD=           # Laragon/XAMPP default: kosong
   ```
3. Uji koneksi:
   ```bash
   php artisan migrate:status
   ```
   Jika tabel `migrations` tampil/terbentuk tanpa error, koneksi berhasil.

### 5b. Database di VPS yang sama dengan aplikasi
Buat database + **user khusus** (bukan root), lalu isi `.env` dengan `DB_HOST=127.0.0.1`. Langkahnya ada di [bagian 10](#10-deploy-ke-vps-linux-langkah-demi-langkah).

### 5c. Managed database di cloud (MySQL terkelola)
Penyedia cloud biasanya memberi: **host**, **port**, **nama database**, **username**, **password**, dan terkadang **sertifikat CA (SSL)**.

```env
DB_CONNECTION=mysql
DB_HOST=alamat-host-dari-penyedia
DB_PORT=3306
DB_DATABASE=katniss_store
DB_USERNAME=user_aplikasi
DB_PASSWORD=password_kuat_anda
# Jika penyedia mewajibkan SSL, unduh sertifikat CA dan isi path-nya:
MYSQL_ATTR_SSL_CA=/path/ke/ca-certificate.crt
```

Checklist agar berhasil terhubung:
- Server aplikasi (alamat IP-nya) **diizinkan** di firewall / *trusted sources* database.
- Port 3306 terbuka dari server aplikasi ke database (jangan dibuka ke seluruh internet).
- Karakter spesial di password: bungkus dengan tanda kutip di `.env`, mis. `DB_PASSWORD="p@ss#word"`.
- Jalankan `php artisan migrate --force` dari server aplikasi untuk membuat tabel di database tersebut.

Mengganti database **tidak** butuh perubahan kode, cukup ubah `.env` lalu `php artisan config:clear` (atau `config:cache` di production).

## 6. Akun demo & membuat admin

Saat `APP_ENV=local`, `php artisan migrate --seed` membuat akun **khusus development** berikut:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@katnissstore.test` | `Admin12345` |
| Customer | `customer@katnissstore.test` | `Customer12345` |

Seeder juga membuat 3 game **fiktif** + nominal contoh dan kode promo `DEMO10` (10%, min. Rp 20.000). Semuanya hanya data contoh.

> **Ganti atau hapus akun demo sebelum production.** Di `APP_ENV=production` seeder **tidak** membuat akun/data contoh. Buat admin pertama dengan perintah aman (password diketik interaktif, tidak disimpan di file):
>
> ```bash
> php artisan katniss:create-admin email@anda.com --name="Nama Anda"
> ```

Untuk mengisi data contoh secara manual: `php artisan db:seed --class=SampleDataSeeder`.

**Mencoba alur lengkap (demo):**
1. Login sebagai customer → pilih game → pilih nominal + isi Player ID → Checkout → *Buat Pesanan*.
2. Di halaman pesanan klik **Simulasikan bayar berhasil**. Status menjadi *Dibayar* & *Diproses*.
3. Login sebagai admin → *Pesanan* → buka pesanan → **Jalankan top-up** (simulasi) → status *Selesai*.
4. Player ID `000000` mensimulasikan top-up gagal.

## 7. Menjalankan test

```bash
php artisan test
```

Test memakai SQLite in-memory (tidak menyentuh database MySQL Anda) dan tidak butuh `npm run build`. Cakupan: akses guest/customer/admin, isolasi pesanan antar customer, validasi Player ID/Server ID/produk, harga dihitung server, snapshot harga, produk/game nonaktif, idempotensi checkout, promo, simulasi pembayaran, webhook (signature, duplikasi), transisi status, top-up tidak diproses dua kali, CRUD admin, laporan CSV.

Opsional – merapikan kode: `./vendor/bin/pint`.

## 8. Struktur folder

```
app/
  Console/Commands/   katniss:create-admin, katniss:cancel-expired-orders
  Contracts/          PaymentGateway, TopUpProvider (antarmuka integrasi)
  Enums/              Role, OrderStatus, PaymentStatus (+ aturan transisi)
  Http/Controllers/   Publik, Auth/, Admin/
  Http/Middleware/    EnsureUserIsAdmin
  Http/Requests/      Form Request (validasi) + Admin/
  Jobs/               ProcessTopUpJob
  Models/             User, Game, Product, Order, OrderItem, Payment, Promotion, Setting, ActivityLog, WebhookEvent
  Policies/           OrderPolicy
  Services/           OrderService (logika bisnis), PromotionService, SettingService, ActivityLogger,
                      Payments/FakePaymentGateway, TopUp/FakeTopUpProvider
  Support/helpers.php rupiah(), setting(), upload_url(), is_simulation()
config/               konfigurasi; config/katniss.php = pengaturan khusus aplikasi
database/             migrations, factories, seeders
docker/               php.ini, entrypoint, contoh Nginx & Supervisor untuk VPS
docs/                 DEPLOYMENT.md, INTEGRATION.md, ARCHITECTURE.md
lang/id/              terjemahan pesan validasi
resources/            views (layouts, components, publik, customer, admin), css, js
routes/               web.php, console.php (scheduler)
tests/                Feature & Unit
```

Format uang: disimpan sebagai **bilangan bulat Rupiah** (kolom `unsignedBigInteger`, tanpa float) dan ditampilkan lewat `rupiah()` → `Rp 15.000`.

Detail arsitektur, status pesanan, dan alur ada di [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md).

## 9. Konfigurasi: mail, queue, cache, storage, mode demo

Semua lewat `.env` (lihat `.env.example` yang berkomentar lengkap).

| Topik | Default | Untuk produksi |
|---|---|---|
| **Mail** | `MAIL_MAILER=log` (email reset password ditulis ke `storage/logs/laravel.log`) | `smtp` + `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` |
| **Queue** | `QUEUE_CONNECTION=sync` (langsung dijalankan) | `database` lalu jalankan `php artisan queue:work` (Supervisor, lihat `docker/supervisor-queue.conf`). Tabel `jobs` sudah ada di migrasi. Redis juga didukung. |
| **Cache** | `CACHE_STORE=database` | `redis` bila ada Redis |
| **Session** | `SESSION_DRIVER=database` (aman untuk banyak server) | tambah `SESSION_SECURE_COOKIE=true` di HTTPS |
| **Storage** | `UPLOAD_DISK=public` (lokal, butuh `storage:link`) | `UPLOAD_DISK=s3` agar upload tidak hilang saat redeploy; butuh `composer require league/flysystem-aws-s3-v3 "^3.0"` + variabel `AWS_*` (kompatibel S3: MinIO, R2, dll.) |
| **Pembayaran** | `PAYMENT_DRIVER=fake` (SIMULASI) | implementasikan gateway nyata ([INTEGRATION.md](docs/INTEGRATION.md)) |
| **Top-up** | `TOPUP_DRIVER=fake` (SIMULASI) | implementasikan supplier nyata |
| **Auto top-up** | `TOPUP_AUTO_PROCESS=false` (admin menekan tombol) | `true` = otomatis setelah lunas |
| **Pesanan kedaluwarsa** | `ORDER_EXPIRE_HOURS=24` | dibatalkan otomatis oleh scheduler |
| **Scheduler** | – | cron `* * * * * php artisan schedule:run` |

## 10. Deploy ke VPS Linux (langkah demi langkah)

Contoh untuk **Ubuntu 24.04** (PHP 8.3 tersedia bawaan). Ganti `contoh-domain.com` dengan domain Anda. Panduan ini netral vendor: berlaku untuk VPS mana pun.

### 10.1 Persiapan server
```bash
ssh root@IP_SERVER
adduser deploy && usermod -aG sudo deploy        # user non-root
# (disarankan) pasang SSH key lalu nonaktifkan login password root
apt update && apt upgrade -y
```

Firewall (buka hanya SSH, HTTP, HTTPS – **jangan** buka port 3306 ke publik):
```bash
ufw allow OpenSSH && ufw allow 'Nginx Full' && ufw enable
```

### 10.2 Install paket
```bash
apt install -y nginx mysql-server git unzip curl \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-intl php8.3-bcmath php8.3-gd
# Composer
curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer
# Node.js 20 (hanya untuk build asset; boleh juga build di komputer lokal)
curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && apt install -y nodejs
```
> Ubuntu 22.04 hanya punya PHP 8.1 bawaan: tambahkan PPA `ppa:ondrej/php` untuk PHP 8.3.

### 10.3 Buat database + user khusus
```bash
mysql_secure_installation
mysql -u root -p
```
```sql
CREATE DATABASE katniss_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'katniss'@'localhost' IDENTIFIED BY 'GANTI_DENGAN_PASSWORD_KUAT';
GRANT ALL PRIVILEGES ON katniss_store.* TO 'katniss'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 10.4 Taruh kode di server
Cara paling rapi: push proyek ke repository Git (GitHub/GitLab, **repo private**), lalu:
```bash
sudo mkdir -p /var/www/katniss-store && sudo chown deploy:deploy /var/www/katniss-store
git clone URL_REPO_ANDA /var/www/katniss-store
cd /var/www/katniss-store
```
Alternatif: upload folder proyek lewat `scp`/SFTP (jangan sertakan `.env`, `vendor`, `node_modules`).

### 10.5 Install, konfigurasi, build
```bash
composer install --no-dev --optimize-autoloader
npm install && npm run build          # atau build di lokal lalu upload folder public/build
cp .env.example .env
php artisan key:generate
nano .env
```
Isi `.env` production (contoh):
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://contoh-domain.com
FORCE_HTTPS=true
SESSION_SECURE_COOKIE=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=katniss_store
DB_USERNAME=katniss
DB_PASSWORD=GANTI_DENGAN_PASSWORD_KUAT

MAIL_MAILER=smtp        # + MAIL_HOST/PORT/USERNAME/PASSWORD dari penyedia email Anda
LOG_LEVEL=info
```

### 10.6 Migrasi, link storage, admin, permission
```bash
php artisan migrate --force
php artisan db:seed --class=SettingSeeder --force     # pengaturan toko dasar (tanpa data contoh)
php artisan storage:link
php artisan katniss:create-admin email@anda.com --name="Nama Anda"

sudo chown -R deploy:www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache

php artisan config:cache && php artisan route:cache && php artisan view:cache
```

### 10.7 Nginx
Salin `docker/nginx-vps.conf` ke `/etc/nginx/sites-available/katniss-store`, sesuaikan `server_name` dan path, lalu:
```bash
sudo ln -s /etc/nginx/sites-available/katniss-store /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```
**Web root harus `.../public`** – folder lain (termasuk `.env`) tidak boleh dapat diakses publik.

### 10.8 HTTPS
Lihat [bagian 12](#12-domain-dns-https-firewall) (Let's Encrypt/Certbot).

### 10.9 Scheduler & queue
```bash
sudo crontab -u www-data -e
# tambahkan baris:
* * * * * cd /var/www/katniss-store && php artisan schedule:run >> /dev/null 2>&1
```
Queue worker (hanya jika `QUEUE_CONNECTION=database/redis`): pakai Supervisor dengan contoh `docker/supervisor-queue.conf`.

### 10.10 Verifikasi
Buka `https://contoh-domain.com`, login admin, tambah game & produk. `https://contoh-domain.com/health` harus menampilkan `{"status":"ok"}`.

## 11. Deploy ke cloud / managed hosting / Docker

Prinsipnya sama untuk penyedia apa pun: **(1)** siapkan runtime PHP 8.2+ (atau jalankan image Docker), **(2)** siapkan MySQL terkelola, **(3)** isi variabel environment, **(4)** jalankan perintah build & migrasi, **(5)** arahkan domain. Ringkasan di bawah; versi lengkap ada di [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md).

**Docker Compose (VPS kecil / demo):**
```bash
cp .env.docker.example .env
# isi password DB. Buat APP_KEY dari komputer lokal (setelah composer install):
#   php artisan key:generate --show
# lalu tempel hasilnya ke APP_KEY= di file .env
docker compose up -d --build        # aplikasi di http://localhost:8080
docker compose exec app php artisan katniss:create-admin email@anda.com
```
(Gunakan `docker compose --profile worker up -d` untuk menambah queue worker + scheduler.)

**Platform managed (PaaS / container service):** gunakan `Dockerfile` di repo, set variabel environment dari `.env.example` lewat panel penyedia (jangan commit `.env`), arahkan ke managed MySQL (lihat 5c), jalankan `php artisan migrate --force` sebagai *release command*, dan gunakan **object storage S3-compatible** (`UPLOAD_DISK=s3`) karena disk container bisa hilang saat redeploy. Gunakan `LOG_CHANNEL=stderr` agar log terbaca di panel.

**Shared hosting dengan cPanel:** arahkan *document root* domain ke folder `public/`. Jika tidak bisa diubah, jangan menaruh seluruh proyek di `public_html`; taruh proyek di luar `public_html` lalu isi `public_html` hanya dengan isi folder `public/` dan sesuaikan path `require` di `index.php`. Butuh PHP 8.2+ dan akses SSH/Terminal untuk `composer install` dan migrasi (atau jalankan di lokal lalu unggah `vendor/`).

## 12. Domain, DNS, HTTPS, firewall

1. **Domain:** beli di registrar mana pun.
2. **DNS:** buat record **A** untuk `contoh-domain.com` dan `www` ke IP publik server (atau **CNAME** ke alamat yang diberikan platform cloud). Propagasi bisa beberapa menit–jam.
3. **HTTPS (VPS + Nginx):**
   ```bash
   sudo apt install -y certbot python3-certbot-nginx
   sudo certbot --nginx -d contoh-domain.com -d www.contoh-domain.com
   sudo systemctl status certbot.timer     # perpanjangan otomatis
   ```
   Platform managed biasanya menyediakan sertifikat otomatis di panel domain.
4. Setelah HTTPS aktif: `APP_URL=https://...`, `FORCE_HTTPS=true`, `SESSION_SECURE_COOKIE=true`, lalu `php artisan config:cache`.
5. **Firewall:** hanya buka 22 (SSH, sebaiknya dibatasi IP), 80, 443. Database tidak dibuka ke publik; pada managed DB gunakan *allow-list* IP server aplikasi / jaringan privat.
6. **Reverse proxy / load balancer:** aplikasi dikonfigurasi memercayai header proxy (`bootstrap/app.php`, `trustProxies(at: '*')`) agar HTTPS & IP klien terbaca benar. Pastikan server aplikasi **tidak** diakses langsung dari internet tanpa proxy tepercaya, atau ubah pengaturan itu ke daftar IP proxy Anda.

## 13. Checklist production

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` unik (jangan pakai kunci contoh)
- [ ] `.env` **tidak** ada di Git; hanya `public/` yang terekspos web server
- [ ] HTTPS aktif; `FORCE_HTTPS=true`; `SESSION_SECURE_COOKIE=true`
- [ ] Akun demo tidak ada; admin dibuat dengan `katniss:create-admin` (password kuat)
- [ ] User database khusus (bukan root), password kuat, port DB tidak publik
- [ ] `php artisan migrate --force`, `config:cache`, `route:cache`, `view:cache`
- [ ] Permission `storage/` dan `bootstrap/cache/` benar; `storage:link` dibuat
- [ ] Mail SMTP sungguhan (atau fitur reset password dimatikan)
- [ ] Upload memakai object storage jika server/container bisa diganti (`UPLOAD_DISK=s3`)
- [ ] Scheduler (cron) aktif; queue worker aktif bila memakai queue
- [ ] Backup database terjadwal **dan** pernah diuji restore
- [ ] `PAYMENT_DRIVER`/`TOPUP_DRIVER` sesuai kenyataan; label SIMULASI hilang hanya bila integrasi nyata sudah diuji di sandbox
- [ ] `PAYMENT_WEBHOOK_SECRET` acak panjang bila webhook dipakai
- [ ] Halaman Privasi & Syarat Layanan sudah ditinjau/diganti (yang ada hanya draf contoh)
- [ ] Monitoring `/health` dan pemantauan log

## 14. Backup & restore

**Backup (mysqldump):**
```bash
mysqldump -u katniss -p --single-transaction --routines katniss_store | gzip > backup-$(date +%F).sql.gz
```
Otomatis harian (cron, simpan di luar web root, mis. `/var/backups/katniss`), lalu salin ke lokasi lain (object storage / server lain). Jangan simpan password di perintah cron: gunakan file `~/.my.cnf` (mode `600`) atau variabel lingkungan.

**Restore:**
```bash
gunzip < backup-2026-01-01.sql.gz | mysql -u katniss -p katniss_store
```
Backup juga folder `storage/app/public` (upload) bila `UPLOAD_DISK=public`. Pada managed DB, aktifkan *automated backup/snapshot* penyedia dan tetap lakukan dump berkala. **Uji restore** di database terpisah secara berkala.

## 15. Monitoring, log, troubleshooting, update & rollback

**Log:** `storage/logs/laravel.log` (atau stderr bila `LOG_CHANNEL=stderr`). Log Nginx: `/var/log/nginx/error.log`. Pantau `/health` dengan layanan uptime apa pun. Password/token tidak pernah ditulis ke log oleh aplikasi.

**Troubleshooting umum**

| Gejala | Penyebab / solusi |
|---|---|
| `No application encryption key` | `php artisan key:generate` |
| `Base table or view not found: sessions` | belum migrasi: `php artisan migrate` |
| `SQLSTATE[HY000] [1045] Access denied` | username/password DB di `.env` salah; `php artisan config:clear` |
| `SQLSTATE[HY000] [2002]` | MySQL belum jalan / `DB_HOST` salah / firewall |
| `Vite manifest not found` | belum `npm run build` (atau `npm run dev` belum jalan) |
| Gambar upload tidak tampil | `php artisan storage:link` dan cek `APP_URL` |
| 403/404 di semua halaman selain beranda | web root bukan `public/` atau rewrite Nginx/Apache belum aktif |
| 500 di server | cek `storage/logs/laravel.log`; periksa permission `storage/` & `bootstrap/cache/` |
| Perubahan `.env` tidak berefek | `php artisan config:clear` (lalu `config:cache` lagi) |
| Email reset password tidak sampai | `MAIL_MAILER=log` hanya menulis ke log; atur SMTP |

**Update aplikasi**
```bash
cd /var/www/katniss-store
php artisan down                      # mode maintenance
git pull
composer install --no-dev --optimize-autoloader
npm install && npm run build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```
Selalu **backup database sebelum update**.

**Rollback:** `php artisan down` → `git checkout <commit/tag sebelumnya>` → `composer install --no-dev` → build asset → bila migrasi baru perlu dibatalkan: `php artisan migrate:rollback --step=N` (atau restore backup database) → cache ulang → `php artisan up`. Beri tag Git pada setiap rilis agar rollback mudah.

## 16. Biaya

Komponen yang dapat menimbulkan biaya (besarnya tergantung penyedia, wilayah, dan paket – cek halaman harga masing-masing, tidak ada angka tetap di sini):
VPS/server aplikasi atau layanan container, database MySQL terkelola (jika tidak memakai MySQL di VPS), object storage + transfer data keluar, domain (tahunan), sertifikat SSL (Let's Encrypt gratis), layanan email SMTP, backup/snapshot tambahan, Redis terkelola (opsional), monitoring/log berbayar (opsional), dan **biaya transaksi payment gateway / supplier top-up** bila nanti diintegrasikan.

## 17. Known limitations

- **Belum teruji di lingkungan pembuatan** (lihat catatan di bagian 2); jalankan `composer install`, `php artisan migrate --seed`, `php artisan test`, `npm run build` dan laporkan error yang muncul.
- Pembayaran & top-up **hanya simulasi**; tidak ada integrasi gateway/supplier nyata, tidak ada rekonsiliasi atau refund otomatis (refund hanya penanda status oleh admin).
- Tidak ada verifikasi email, 2FA, atau CAPTCHA; rate limiting hanya berbasis IP/akun.
- Checkout hanya untuk user login, satu produk per pesanan (struktur `order_items` sudah siap untuk lebih dari satu).
- Top-up ke supplier nyata dijalankan di dalam transaksi database dengan penguncian baris (mencegah ganda, tetapi panggilan API lambat menahan baris); untuk skala besar pertimbangkan pola outbox.
- Halaman Privasi & Syarat hanya **draf contoh**; tidak ada fitur ekspor/hapus data pengguna.
- Role hanya `customer` dan `admin` (tanpa izin granular).
- Pengaturan "percaya semua proxy" cocok di belakang reverse proxy, perlu dipersempit bila diekspos langsung.
- Aplikasi tidak dapat dijamin "100% aman"; lakukan pembaruan dependency berkala (`composer audit`, `npm audit`) dan tinjauan keamanan sebelum menerima transaksi nyata.
- Upload gambar hanya JPG/PNG/WebP (SVG sengaja ditolak); tidak ada pemrosesan/resize gambar.

## Lisensi
MIT (kode aplikasi). Kerangka Laravel berlisensi MIT.
# Katniss-Store
