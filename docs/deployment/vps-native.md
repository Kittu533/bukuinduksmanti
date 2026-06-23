# Deploy Laravel ke VPS Tanpa Docker

Panduan ini buat repo ini, dengan asumsi VPS pakai Ubuntu 22.04 atau 24.04, Nginx, PHP 8.2 FPM, dan MySQL/MariaDB.

## 1. Provisioning VPS

Pasang paket dasar:

```bash
sudo apt update
sudo apt install -y nginx mysql-server unzip git curl
sudo apt install -y php8.2-fpm php8.2-cli php8.2-common php8.2-mysql php8.2-mbstring php8.2-xml php8.2-zip php8.2-gd php8.2-bcmath php8.2-curl
```

Pasang Composer:

```bash
cd /tmp
curl -sS https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

Pasang Node.js 20:

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
node -v
npm -v
```

## 2. Buat database

Masuk ke MySQL:

```bash
sudo mysql
```

Buat database dan user:

```sql
CREATE DATABASE buku_induk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'buku_induk_user'@'localhost' IDENTIFIED BY 'ganti-password-db';
GRANT ALL PRIVILEGES ON buku_induk.* TO 'buku_induk_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 3. Clone project ke VPS

```bash
cd /var/www
sudo git clone <url-repo-kamu> buku-induk
sudo chown -R $USER:$USER /var/www/buku-induk
cd /var/www/buku-induk
```

## 4. Siapkan environment production

Copy template:

```bash
cp .env.production.example .env
```

Edit `.env`:

```env
APP_URL=https://domain-kamu.sch.id
DB_DATABASE=buku_induk
DB_USERNAME=buku_induk_user
DB_PASSWORD=password-asli-db
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email-sekolah
MAIL_PASSWORD=app-password-smtp
MAIL_FROM_ADDRESS=email-sekolah
RECAPTCHA_SECRET_KEY=isi-jika-dipakai
RECAPTCHA_SITE_KEY=isi-jika-dipakai
```

Generate app key:

```bash
php artisan key:generate
```

## 5. Install dependency aplikasi

Install PHP dependency:

```bash
composer install --no-dev --optimize-autoloader
```

Build asset Vite:

```bash
npm install
npm run build
```

## 6. Jalankan setup Laravel

Repo ini memakai `SESSION_DRIVER=database` dan `CACHE_STORE=database`, jadi migrasi wajib jalan.

```bash
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Set permission:

```bash
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

## 7. Konfigurasi Nginx

Copy file config yang sudah disiapkan repo ini:

```bash
sudo cp deploy/nginx/buku-induk.conf /etc/nginx/sites-available/buku-induk
```

Edit `server_name` bila perlu:

```bash
sudo nano /etc/nginx/sites-available/buku-induk
```

Aktifkan site:

```bash
sudo ln -s /etc/nginx/sites-available/buku-induk /etc/nginx/sites-enabled/buku-induk
sudo nginx -t
sudo systemctl reload nginx
sudo systemctl restart php8.2-fpm
```

## 8. Aktifkan HTTPS

Pasang Certbot:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d domain-kamu.sch.id -d www.domain-kamu.sch.id
```

## 9. Workflow update setelah ada perubahan code

Setiap habis `git pull`, jalankan:

```bash
cd /var/www/buku-induk
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
sudo systemctl reload nginx
sudo systemctl restart php8.2-fpm
```

## 10. Catatan khusus repo ini

- Gunakan `QUEUE_CONNECTION=sync` dulu di production.
Alasannya: repo ini belum punya migrasi tabel `jobs` dan `failed_jobs`, jadi `database` queue akan error kalau worker dijalankan.
- Setup SMTP mengikuti panduan di [SETUP_EMAIL.md](/Users/user/project-2026/BukuIndukSiswa2/BukuInduk/SETUP_EMAIL.md).
- Jika upload file user nantinya ditaruh di `storage/app/public`, `php artisan storage:link` wajib tetap ada.
- Jika domain belum siap, isi `APP_URL` sementara dengan `http://IP-VPS`, lalu ganti lagi setelah HTTPS aktif.

## 11. Checklist cepat kalau 502/500

```bash
sudo journalctl -u php8.2-fpm -n 100 --no-pager
sudo tail -n 100 /var/log/nginx/buku-induk.error.log
tail -n 100 storage/logs/laravel.log
php artisan about
```

Kalau mau, langkah berikutnya saya bisa bantu bikin checklist yang lebih spesifik untuk provider VPS kamu atau bantu review `.env` production kamu sebelum naik.
