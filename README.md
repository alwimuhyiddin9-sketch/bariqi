# Bariqi.co — Laravel + MySQL

Sistem Manajemen Data Barang, Persediaan, dan Penjualan.
Dibangun dengan **Laravel 12 + MySQL + Blade + Bootstrap 5 + Chart.js**.

## Persyaratan

- **PHP 8.2+** (via XAMPP)
- **Composer** (https://getcomposer.org)
- **MySQL** (via XAMPP)
- Ekstensi PHP: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`

## Cara Install

### 1. Copy ke folder XAMPP

Copy seluruh folder `laravel-bariqi` ke:
```
C:\xampp\htdocs\laravel-bariqi
```

### 2. Buat database MySQL

Buka **phpMyAdmin** (http://localhost/phpmyadmin), buat database baru:
```
Nama database: bariqi_co
Collation: utf8mb4_unicode_ci
```

### 3. Konfigurasi environment

Copy file `.env.example` menjadi `.env`:
```bash
cd C:\xampp\htdocs\laravel-bariqi
copy .env.example .env
```

Edit `.env`, pastikan bagian database:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bariqi_co
DB_USERNAME=root
DB_PASSWORD=
```
(Username default XAMPP: `root`, password kosong)

### 4. Install dependencies via Composer

Buka **Command Prompt** atau **Terminal**, lalu:
```bash
cd C:\xampp\htdocs\laravel-bariqi
composer install
```

### 5. Generate application key

```bash
php artisan key:generate
```

### 6. Jalankan migration + seeder

```bash
php artisan migrate --seed
```

Ini akan membuat semua tabel dan mengisi data contoh (admin, kategori, barang, varian, dll).

### 7. Jalankan aplikasi

**Opsi A — Via XAMPP Apache** (akses: http://localhost/laravel-bariqi/public)

**Opsi B — Via Artisan server** (lebih cepat):
```bash
php artisan serve
```
Akses: http://localhost:8000

## Login

- **Nama Pengguna:** `admin`
- **Kata Sandi:** `Bariqi2026!`

## Fitur

- Beranda (dashboard) dengan grafik penjualan, kartu ringkasan, barang terlaris, peringatan stok
- Data Barang (CRUD + varian warna/ukuran + foto)
- Kategori Barang (CRUD)
- Varian Barang (CRUD terpisah)
- Persediaan Barang (barang masuk/keluar/penyesuaian)
- Riwayat Stok (semua perubahan stok)
- Penjualan Online (catat pesanan dari Shopee/Tokopedia/TikTok/Instagram/WhatsApp)
- Kasir (POS dengan keranjang, pembayaran tunai/transfer/QRIS, struk)
- Riwayat Transaksi (filter, detail, batalkan, retur)
- Pengeluaran (CRUD + ringkasan per kategori)
- Laporan (Barang, Persediaan, Penjualan, Keuangan/Laba-Rugi) + Unduh PDF
- Pengaturan (profil toko, akun admin, preferensi)

## Aturan Stok

- Stok otomatis berkurang saat pesanan dikonfirmasi (status: Diproses/Dikirim/Selesai)
- Pembatalan mengembalikan stok yang sudah dikurangi
- Retur sebagian didukung
- Tidak ada pengurangan stok ganda
- Stok tidak bisa negatif

## Struktur Database

| Tabel | Fungsi |
|-------|--------|
| `users` | Akun administrator |
| `store_settings` | Profil toko |
| `app_settings` | Preferensi aplikasi |
| `categories` | Kategori barang |
| `products` | Barang utama |
| `product_variants` | Varian (warna/ukuran) |
| `sales` | Transaksi penjualan |
| `sale_items` | Detail item per transaksi |
| `stock_movements` | Riwayat perubahan stok |
| `expenses` | Pengeluaran operasional |

## Troubleshooting

**Error: Class 'PDO' not found**
→ Aktifkan `extension=pdo_mysql` di `php.ini` XAMPP (hapus `;` di depan).

**Error: No application encryption key has been specified**
→ Jalankan `php artisan key:generate`.

**Error: Access denied for user 'root'**
→ Cek kembali konfigurasi `DB_USERNAME` dan `DB_PASSWORD` di `.env`.

**Halaman blank / 500 error**
→ Jalankan `php artisan config:clear`, `php artisan cache:clear`, `php artisan view:clear`.

**DomPDF error saat unduh laporan**
→ Pastikan `barryvdh/laravel-dompdf` terinstall: `composer require barryvdh/laravel-dompdf`.

## Catatan

- Aplikasi ini menggunakan **satu akun administrator** (sesuai permintaan). Tidak ada registrasi publik.
- Bahasa: Bahasa Indonesia di seluruh antarmuka.
- Mata uang: Rupiah (IDR).
- Data demo sudah disediakan via seeder. Hapus jika ingin mulai kosong: `php artisan migrate:fresh` (tanpa `--seed`).

## Lisensi

Internal use — Bariqi.co.
