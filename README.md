# Langkat IAMT CMDB

Aplikasi CMDB untuk manajemen aset digital Kabupaten Langkat. Stack utama:

- Laravel untuk backend API, migration, seed, audit log, dan service impact analysis.
- Vue 3 + Vite untuk dashboard operasional modern.
- MySQL dengan struktur awal mengacu pada `schema.sql`.

## Fitur

- Dashboard aset digital: kapasitas infrastruktur, status layanan, coverage keamanan, dan ringkasan kepatuhan.
- Manajemen Pusat Data / DC: gedung atau ruang DC, rack, server baremetal, VM / CT, ISP, IP address, dan UPS / power backup.
- Manajemen aplikasi: aplikasi, tech stack, klasifikasi fungsi, dokumen aplikasi, klasifikasi aset data, dan interoperabilitas.
- Klasifikasi data berdasarkan skor kerahasiaan, integritas, dan ketersediaan sesuai acuan Permenkomdigi No. 5 Tahun 2025.
- Manajemen keamanan informasi: media pencadangan, jadwal pencadangan, dan SOC tools beserta coverage DC, server, VM, dan aplikasi.
- Mapping aplikasi ke VM, server, dan IP address.
- Impact analysis server ke aplikasi terdampak.
- Compliance check PSE/SPBE ringan dan security gap.
- Audit log umum untuk perubahan data.
- Riwayat perubahan aset Server dan VM dengan nilai lama-baru, alasan perubahan, operator, IP address, dan user agent.
- Autentikasi API berbasis token, login screen, dan modul Pengguna & Role dengan pilihan `full` dan `read_only`.
- Seed data contoh Kabupaten Langkat.

## Setup

```bash
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan migrate --seed
composer dev
```

Alternatif menjalankan backend dan frontend terpisah:

```bash
php artisan serve
npm run dev
```

Build frontend produksi:

```bash
npm run build
```

Menjalankan test:

```bash
php artisan test
```

## Mode Demo Lokal

Jika MySQL belum berjalan, aplikasi bisa dicoba dengan SQLite:

```bash
New-Item -ItemType File -Force database/database.sqlite
$env:DB_CONNECTION="sqlite"
$env:DB_DATABASE="C:\Users\user\Desktop\entahapa\iamt\database\database.sqlite"
php artisan migrate:fresh --seed
php artisan serve
```

Extension PHP yang dibutuhkan: `openssl`, `curl`, `fileinfo`, `mbstring`, `pdo_mysql`, `zip`. Untuk mode SQLite lokal tambahkan `pdo_sqlite` dan `sqlite3`.

## Akun Demo

Seeder membuat dua akun awal:

```text
Full access : admin@langkatkab.go.id / password
Read only   : viewer@langkatkab.go.id / password
```

Role `full` dapat membaca dan mengubah data. Role `read_only` hanya dapat membaca dashboard, tabel, mapping, compliance, dan audit.

## Catatan Database

Nama database default mengikuti acuan awal:

```env
DB_DATABASE=iamt_refactor
```

Pastikan database MySQL sudah tersedia sebelum menjalankan migration.
