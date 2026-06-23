# Laravel VPS Deploy Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menyiapkan bundle deploy native VPS untuk aplikasi Laravel ini tanpa Docker.

**Architecture:** Deploy memakai Ubuntu VPS dengan Nginx sebagai web server, PHP 8.2 FPM untuk runtime Laravel, dan MySQL/MariaDB sebagai database. Repo menyiapkan template environment production, contoh konfigurasi Nginx, dan panduan deploy supaya aplikasi bisa dipasang ulang dengan langkah yang konsisten.

**Tech Stack:** Laravel 11, PHP 8.2 FPM, Nginx, MySQL/MariaDB, Composer, Node.js/Vite

---

### Task 1: Siapkan template environment production

**Files:**
- Create: `/.env.production.example`

- [ ] **Step 1: Tulis template env production**

Masukkan nilai default production yang aman:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://bukuinduk.example.sch.id
DB_CONNECTION=mysql
QUEUE_CONNECTION=sync
SESSION_DRIVER=database
CACHE_STORE=database
```

- [ ] **Step 2: Pastikan env menyesuaikan kebutuhan repo**

Cantumkan juga SMTP, reCAPTCHA, dan filesystem lokal karena aplikasi memang memakainya:

```env
MAIL_MAILER=smtp
RECAPTCHA_SECRET_KEY=
RECAPTCHA_SITE_KEY=
FILESYSTEM_DISK=local
```

### Task 2: Siapkan konfigurasi Nginx untuk VPS

**Files:**
- Create: `/deploy/nginx/buku-induk.conf`

- [ ] **Step 1: Tulis server block Laravel**

Gunakan root ke folder `public` dan teruskan request PHP ke socket PHP-FPM:

```nginx
root /var/www/buku-induk/public;
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
location ~ \.php$ {
    fastcgi_pass unix:/run/php/php8.2-fpm.sock;
}
```

- [ ] **Step 2: Tambahkan proteksi file sensitif**

Blok akses ke file tersembunyi selain `.well-known`:

```nginx
location ~ /\.(?!well-known).* {
    deny all;
}
```

### Task 3: Tulis panduan deploy native VPS

**Files:**
- Create: `/docs/deployment/vps-native.md`

- [ ] **Step 1: Dokumentasikan provisioning server**

Tuliskan paket Ubuntu yang perlu dipasang:

```bash
sudo apt update
sudo apt install -y nginx mysql-server unzip git curl
sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-zip php8.2-gd php8.2-bcmath php8.2-curl
```

- [ ] **Step 2: Dokumentasikan deploy aplikasi**

Tuliskan langkah clone, composer install, npm build, `.env`, migrate, storage link, permission, dan restart service.

- [ ] **Step 3: Dokumentasikan catatan khusus repo ini**

Jelaskan bahwa:

```text
- SESSION_DRIVER dan CACHE_STORE memakai database
- QUEUE_CONNECTION sebaiknya sync karena migrasi jobs/failed_jobs belum ada
- setup SMTP mengikuti SETUP_EMAIL.md
```

### Task 4: Verifikasi bundle deploy

**Files:**
- Review: `/.env.production.example`
- Review: `/deploy/nginx/buku-induk.conf`
- Review: `/docs/deployment/vps-native.md`

- [ ] **Step 1: Baca ulang isi file**

Pastikan semua path konsisten memakai `/var/www/buku-induk`.

- [ ] **Step 2: Cek tidak ada instruksi Docker**

Bundle final harus murni native VPS sesuai permintaan user.
