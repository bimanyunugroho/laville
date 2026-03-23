![alt text](thumbnail.png)

# 🌸 Laville - Sistem Informasi Penjualan Parfume

**Laville** adalah aplikasi web berbasis Laravel yang dirancang untuk membantu pengelolaan penjualan parfume secara modern, cepat, dan efisien.
Aplikasi ini menyediakan fitur monitoring penjualan, manajemen produk, transaksi, hingga analisis performa bisnis dalam satu dashboard yang user-friendly.

---

## ✨ Fitur Utama

* 📊 **Dashboard Overview**

    * Total Profit
    * Conversion Rate
    * Total Orders
    * Total Purchase
    * Grafik transaksi (real-time)
    * Sales by Channel (Offline, Shopee, Tokopedia, Instagram)
    * Dashboard data Daily, Weekly, Monthly, Yearly

* 🧾 **Manajemen Transaksi**

    * Pencatatan penjualan (Order & Pelanggan)
    * Riwayat transaksi
    * Tracking order

* 📦 **Inventory Management**

    * Order Pembelian (PO)
    * Penerimaan Barang
    * Pengeluaran Barang
    * Stock Opname
    * Kartu Stock

* 🗂️ **Master Data**

    * Data produk parfume
    * Konversi satuan produk per-unit
    * Supplier

* ⚙️ **Pengaturan Sistem**

    * Setting periode
    * Konfigurasi aplikasi

* 📈 **Analitik Penjualan**

    * Grafik performa penjualan

---

## 🧑‍💻 Tech Stack

* Laravel v12
* PHP ≥ 8.2
* PostgreSQL
* Node.js & NPM
* Tailwind CSS
* Chart.js / ApexCharts

---

## ⚙️ Prasyarat

Pastikan sudah menginstall:

* PHP 8.2 atau lebih baru
* Composer (latest)
* Node.js (latest)
* Database (PostgreSQL)
* Laragon / XAMPP / sejenisnya

---

## 🚀 Instalasi

### 1. Clone Repository

```bash
git clone git@github.com:bimanyunugroho/laville.git
cd laville
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Setup Environment

```bash
cp .env.example .env
```

Edit file `.env`:

```env
APP_NAME=Laville
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://laville.local
APP_TIMEZONE=Asia/Jakarta
``` 

---

### 4. Generate App Key

```bash
php artisan key:generate
```

---

### 5. Konfigurasi Database

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laville_db
DB_USERNAME=postgres
DB_PASSWORD=
```

---

### 6. Migrasi & Seeder

```bash
php artisan migrate
php artisan db:seed
```

---

### 7. Jalankan Aplikasi

```bash
composer run dev
```

Akses di browser:

```
http://laville.local
```

---

## 🔑 Default Login

```
Email    : admin@gmail.com
Password : admin12345
```

---

## 📌 Struktur Fitur

* Dashboard → Monitoring bisnis
* Setting Periode → Set periode awal untuk transaksi
* Master Data → Data produk & Unit & Konversi produk by unit
* Transactions → Data pelanggan & penjualan
* Inventory → Stok barang
* Settings → Konfigurasi sistem

---

## 📄 Lisensi

© 2024 - 2024 @abisa.officiall - All Rights Reserved.

---
