# 🌐 Global Logistics Dashboard

**Platform Monitoring Risiko Rantai Pasok Global — Tugas Akhir**

[![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.x-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)

> Platform yang mengumpulkan data dari berbagai API publik (cuaca, ekonomi, kurs, berita) dan menggabungkannya menjadi satu sistem skor risiko per negara — untuk membantu keputusan bisnis seperti memilih negara asal impor dengan risiko paling rendah.

---

## 📋 Daftar Isi

- [Live Demo & Akses Admin](#live-demo--akses-admin)
- [Tentang Proyek](#tentang-proyek)
- [Teknologi yang Digunakan](#teknologi-yang-digunakan)
- [Sumber Data & API Eksternal](#sumber-data--api-eksternal)
- [Sistem Risk Scoring](#sistem-risk-scoring)
- [Kelengkapan Data](#kelengkapan-data)
- [Struktur Database](#struktur-database)
- [Artisan Commands](#artisan-commands)
- [Dokumentasi API](#dokumentasi-api)
- [Cara Instalasi](#cara-instalasi)
- [Yang Masih Perlu Dikerjakan](#yang-masih-perlu-dikerjakan)
- [Kontribusi](#kontribusi)

---

## 🌐 Live Demo & Akses Admin

Aplikasi ini sudah di-hosting dan dapat diakses secara langsung melalui tautan berikut:
👉 **[Live Demo Dashboard Internasional](https://dashboardinternasional-production.up.railway.app/)**

**Kredensial Login Admin:**
- **Email:** `ragilrifkyabimayu@gmail.com`
- **Password:** `Ragil123`

---

## 🌟 Tentang Proyek

**Global Logistics Dashboard** adalah platform *supply chain risk intelligence* yang menarik data secara otomatis dari beberapa API publik, mengolahnya, lalu menghasilkan skor risiko per negara. Tujuannya adalah membantu pengambilan keputusan bisnis — misalnya, menentukan dari negara mana sebaiknya melakukan impor berdasarkan tingkat risiko geopolitik, ekonomi, cuaca, dan sentimen berita terkini.

### Cara Kerja Sistem

```
API Eksternal → Artisan Commands → Database (History) → Risk Scoring → API Response → Frontend
```

1. **Fetch Data** — Custom Artisan command menarik data dari masing-masing API eksternal
2. **Simpan sebagai History** — Data yang berubah (cuaca, kurs, ekonomi) disimpan sebagai baris baru, bukan ditimpa, agar bisa membuat grafik tren
3. **Hitung Skor Risiko** — Formula berbobot dari 4 faktor: cuaca, inflasi, sentimen berita, volatilitas kurs
4. **Sajikan via API** — Endpoint JSON yang dikonsumsi oleh frontend

---

## 🛠️ Teknologi yang Digunakan

| Kategori | Teknologi |
|----------|-----------|
| Backend Framework | Laravel 13.x (PHP 8.5) |
| Database | MySQL (via XAMPP) |
| Frontend (rencana) | Bootstrap 5 + Chart.js + Leaflet.js |
| Autentikasi | Laravel Session Auth + Laravel Sanctum |
| Queue | Database Queue |
| Cache | Database Cache |
| Server Lokal | XAMPP |

---

## 🔌 Sumber Data & API Eksternal

### Negara — `countries.dev`

**Catatan penting:** REST Countries API yang awalnya direncanakan sudah **deprecated**. Pihak provider telah menonaktifkan versi lama dan mewajibkan API key untuk versi baru. Setelah mencari alternatif, ditemukan **[countries.dev](https://countries.dev)** yang masih gratis tanpa API key dengan data yang setara.

```
Endpoint: https://countries.dev/api/countries
Data: nama negara, kode ISO, mata uang, kawasan, ibu kota, bendera
```

### Data Ekonomi — World Bank API

```
Endpoint: https://api.worldbank.org/v2/
Data: GDP, tingkat inflasi, populasi
Tidak perlu API key
```

### Cuaca & Koordinat — Open-Meteo

```
Endpoint cuaca:    https://api.open-meteo.com/v1/forecast
Endpoint geocoding: https://geocoding-api.open-meteo.com/v1/search
Data: suhu, kecepatan angin, kode kondisi cuaca
Tidak perlu API key
```

Geocoding API dari Open-Meteo juga digunakan untuk mencari koordinat (latitude/longitude) berdasarkan nama ibu kota negara.

### Kurs Mata Uang — ExchangeRate-API (Open Access)

```
Endpoint: https://open.er-api.com/v6/latest/USD
Data: kurs semua mata uang terhadap USD dalam satu kali request
Tidak perlu API key (versi open access)
```

### Berita — GNews

```
Endpoint: https://gnews.io/api/v4/
Data: berita internasional + analisis sentimen
Memerlukan: GNEWS_API_KEY (daftar gratis di gnews.io)
Kuota: 100 request/hari (gratis)
```

Karena keterbatasan kuota, berita yang spesifik per negara hanya diambil untuk **60 negara dengan GDP terbesar**. Negara lainnya menggunakan rata-rata sentimen global sebagai pengganti.

### Pelabuhan — Dataset JSON dari GitHub

```
Sumber asli: World Port Index (publikasi resmi NIMA/NGA)
Format yang dipakai: Dataset JSON yang sudah diolah dari GitHub
Alasan: File resmi World Port Index sulit diunduh langsung secara programatik
```

---

## ⚖️ Sistem Risk Scoring

Skor risiko mengikuti formula berbobot dari spesifikasi soal:

```
Total Risk = (Weather × 30%) + (Inflation × 20%) + (News Sentiment × 40%) + (Currency × 10%)
```

Setiap faktor dikonversi ke skala **0–100** terlebih dahulu (100 = paling berisiko):

| Faktor | Bobot | Cara Perhitungan |
|--------|-------|-----------------|
| **Cuaca** | 30% | Berdasarkan `storm_risk`: Low=10, Medium=50, High=90 |
| **Inflasi** | 20% | Linear — makin tinggi inflasi makin besar skor (dicap maksimum pada inflasi 20%) |
| **Sentimen Berita** | 40% | Rasio berita negatif dibanding total berita negara tersebut |
| **Kurs** | 10% | Volatilitas — perubahan kurs antar 2 data terakhir yang tersimpan |

### Kategori Risiko

| Skor | Kategori |
|------|----------|
| < 33 | 🟢 Low Risk |
| 33 – 65 | 🟡 Medium Risk |
| > 65 | 🔴 High Risk |

### Analisis Sentimen Berita

Sentimen dianalisis menggunakan kamus kata yang disimpan di database:
- **`positive_words`** — 20 kata bernilai positif
- **`negative_words`** — 20 kata bernilai negatif

Berita di-scan kata per kata, rasio kemunculan kata negatif vs total menentukan skor sentimen.

---

## 📊 Kelengkapan Data

Tidak semua negara memiliki data lengkap di semua kategori — ini bukan bug, melainkan keterbatasan dari sumber datanya. Beberapa negara/teritori kecil memang tidak terdaftar di World Bank, atau nama negaranya berbeda format antar dataset sehingga sulit dicocokkan secara otomatis.

| Kategori Data | Jumlah Tersedia | Persentase |
|---------------|:---------------:|:----------:|
| Data dasar negara | 250 / 250 | 100% |
| Koordinat (lat/lng) | 247 / 250 | ~99% |
| Data cuaca | ~238 / 250 | ~95% |
| Kurs mata uang | 244 / 249 | ~98% |
| Data ekonomi (GDP, inflasi) | ~212 / 250 | ~85% |
| Data pelabuhan (port matching) | ~228 / 250 | ~91% |

> **Catatan pelabuhan:** Awalnya hanya 71% yang berhasil dicocokkan karena perbedaan penulisan nama negara. Setelah ditambahkan logika alias dan *fuzzy matching*, coverage naik menjadi 91%. Angka ini sudah stabil setelah beberapa kali percobaan fetch ulang.

---

## 🗄️ Struktur Database

Sistem memiliki **14 tabel** yang dibagi menjadi dua kategori:

### Data Statis (tidak berubah, hanya ada satu record per entitas)

| Tabel | Keterangan |
|-------|------------|
| `countries` | Data dasar negara: nama, kode ISO, mata uang, kawasan, ibu kota, koordinat, URL bendera |
| `ports` | Data pelabuhan laut: nama, koordinat, tipe, relasi ke negara |
| `positive_words` | Kamus 20 kata positif untuk analisis sentimen |
| `negative_words` | Kamus 20 kata negatif untuk analisis sentimen |
| `users` | Data pengguna dengan kolom role (user/admin) |
| `watchlists` | Daftar pantau negara per pengguna *(tabel sudah ada, fitur belum diimplementasi)* |
| `articles` | Artikel editorial dari admin *(tabel sudah ada, fitur belum diimplementasi)* |

### Data Time-Series (berubah dari waktu ke waktu, setiap fetch menambah baris baru)

> Desain ini disengaja agar sistem bisa menyimpan riwayat dan menampilkan grafik tren.

| Tabel | Keterangan | Frekuensi Update |
|-------|------------|-----------------|
| `economic_indicators` | GDP, tingkat inflasi, populasi per negara | Sesuai kebutuhan |
| `weather_snapshots` | Snapshot cuaca per negara | Setiap 6 jam |
| `exchange_rates` | Kurs mata uang terhadap USD | Setiap 6 jam |
| `risk_scores` | Hasil perhitungan skor risiko per negara | Setelah data di-refresh |
| `news_cache` | Berita + hasil analisis sentimen | Sesuai kebutuhan |

### Tabel Sistem Laravel

| Tabel | Keterangan |
|-------|------------|
| `cache` | Cache sistem Laravel |
| `jobs` | Antrian pekerjaan latar belakang |
| `sessions` | Sesi pengguna berbasis database |
| `personal_access_tokens` | Token API Sanctum |

### Relasi Antar Tabel

```
countries (pusat)
 ├── economic_indicators  (country_id)
 ├── exchange_rates       (country_id)
 ├── weather_snapshots    (country_id)
 ├── risk_scores          (country_id)
 ├── ports                (country_id)
 ├── news_cache           (country_id)
 └── watchlists           (country_id)

users
 ├── watchlists  (user_id)
 └── articles    (user_id)

positive_words / negative_words
 └── (digunakan oleh logika analisis sentimen, tanpa relasi FK)
```

---

## ⚙️ Artisan Commands

Semua data di-fetch menggunakan custom Artisan command — bukan diinput manual. Urutan pengerjaan penting diperhatikan karena beberapa command bergantung pada data dari command sebelumnya.

| Command | Fungsi | Catatan |
|---------|--------|---------|
| `php artisan fetch:countries` | Ambil data 250 negara dari countries.dev | Jalankan pertama kali |
| `php artisan fetch:economic` | Ambil GDP, inflasi, populasi dari World Bank | Butuh data countries |
| `php artisan fetch:coordinates` | Ambil koordinat lat/lng via Open-Meteo Geocoding | Butuh data countries |
| `php artisan fetch:weather` | Ambil snapshot cuaca real-time | Butuh koordinat |
| `php artisan fetch:exchangerates` | Ambil kurs semua mata uang vs USD | Butuh data countries |
| `php artisan fetch:ports` | Ambil dan cocokkan data pelabuhan | Butuh data countries |
| `php artisan fetch:news` | Ambil berita umum + analisis sentimen | Butuh GNEWS_API_KEY |
| `php artisan match:news-countries` | Cocokkan berita yang sudah ada ke negara yang relevan | Jalankan setelah fetch:news |
| `php artisan fetch:news-country` | Ambil berita spesifik untuk 60 negara GDP terbesar | Butuh GNEWS_API_KEY, hemat kuota |
| `php artisan calculate:risk` | Hitung dan simpan skor risiko berdasarkan semua data | Jalankan terakhir |

### Catatan Penting

- Semua command **aman dijalankan berkali-kali** — akan melewati (skip) data yang sudah ada
- **Pengecualian:** `fetch:weather` dan `fetch:exchangerates` selalu menambah baris baru karena memang didesain untuk menyimpan history, bukan menimpa data lama
- Disarankan menjadwalkan `fetch:weather` dan `fetch:exchangerates` setiap 6 jam untuk data yang selalu segar

---

## 🔌 Dokumentasi API

Semua endpoint dapat diakses melalui prefix `/api/`.

---

### `GET /api/countries`

Mengembalikan daftar semua negara beserta snapshot data terbaru dari masing-masing kategori.

**Response:**
```json
[
  {
    "id": 1,
    "name": "Indonesia",
    "code": "ID",
    "currency_code": "IDR",
    "region": "Asia",
    "capital": "Jakarta",
    "flag_url": "https://...",
    "latitude": -0.7893,
    "longitude": 113.9213,
    "weather_snapshots": [...],
    "risk_scores": [...],
    "economic_indicators": [...]
  }
]
```

---

### `GET /api/countries/{id}`

Mengembalikan profil lengkap satu negara, mencakup:
- Indikator ekonomi terbaru
- Snapshot cuaca terbaru
- Kurs mata uang terbaru
- Skor risiko terbaru
- 5 berita terkini
- Daftar semua pelabuhan

---

### `GET /api/risk`

Mengembalikan skor risiko terbaru semua negara, diurutkan dari skor tertinggi (paling berisiko) ke terendah.

**Response:**
```json
[
  {
    "id": 12,
    "country_id": 45,
    "total_score": 78.4,
    "weather_score": 90,
    "inflation_score": 65,
    "sentiment_score": 82,
    "currency_score": 40,
    "risk_level": "High",
    "calculated_at": "2026-07-18T01:00:00Z",
    "country": { "name": "...", "code": "..." }
  }
]
```

---

### `GET /api/ports`

Mengembalikan semua data pelabuhan beserta informasi negara masing-masing.

**Query Parameters:**

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `country_id` | integer | Filter pelabuhan berdasarkan ID negara |

**Contoh:**
```http
GET /api/ports?country_id=5
```

---

### `GET /api/news`

Mengembalikan hingga 50 berita terkini, diurutkan berdasarkan tanggal terbit.

**Query Parameters:**

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `country_id` | integer | Filter berita berdasarkan ID negara |
| `category` | string | Filter berdasarkan kategori berita |

**Contoh:**
```http
GET /api/news?country_id=5
GET /api/news?category=economy
```

---

### API Terproteksi — Membutuhkan Login

```http
GET    /api/watchlist         # Ambil watchlist pengguna yang sedang login
POST   /api/watchlist         # Tambah negara ke watchlist
DELETE /api/watchlist/{id}    # Hapus entri watchlist
```

---

### API Admin — Khusus Role Admin

```http
POST   /admin/users/{id}/role    # Ubah role pengguna (user/admin)
POST   /admin/articles           # Publikasi artikel baru
DELETE /admin/articles/{id}      # Hapus artikel
POST   /admin/ports              # Tambah data pelabuhan
DELETE /admin/ports/{id}         # Hapus data pelabuhan
```

---

## 🚀 Cara Instalasi

### Prasyarat

| Perangkat Lunak | Versi | Catatan |
|-----------------|-------|---------|
| PHP | 8.5+ | Aktifkan ekstensi: `pdo_mysql`, `curl`, `mbstring` |
| Composer | 2.x | |
| MySQL | 5.7+ / 8.x | Via XAMPP |
| Node.js | 18+ | Untuk build aset frontend |
| XAMPP | Terbaru | Apache + MySQL |

### Langkah Instalasi

```bash
# 1. Clone repositori
git clone https://github.com/username/dashboard_internasional.git
cd dashboard_internasional

# 2. Install dependensi PHP
composer install

# 3. Salin file konfigurasi
cp .env.example .env
# (Windows: copy .env.example .env)
```

### Konfigurasi `.env`

Buka file `.env` dan isi bagian berikut:

```env
APP_NAME="Global Logistics Dashboard"
APP_URL=http://127.0.0.1:8000

# Database MySQL (pastikan MySQL XAMPP sudah berjalan)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dashboard_internasional
DB_USERNAME=root
DB_PASSWORD=

# API Key untuk GNews (daftar gratis di https://gnews.io)
GNEWS_API_KEY=your_gnews_api_key_here
```

### Lanjutkan Instalasi

```bash
# Generate application key
php artisan key:generate

# Buat database di MySQL terlebih dahulu (via phpMyAdmin atau CLI):
# CREATE DATABASE dashboard_internasional;

# Jalankan migrasi
php artisan migrate

# Isi kamus sentimen (wajib sebelum fetch berita)
php artisan db:seed --class=PositiveWordSeeder
php artisan db:seed --class=NegativeWordSeeder
```

### Fetch Data (Jalankan Berurutan)

```bash
php artisan fetch:countries       # ~250 negara dari countries.dev
php artisan fetch:economic        # GDP, inflasi, populasi dari World Bank
php artisan fetch:coordinates     # Koordinat lat/lng via Open-Meteo Geocoding
php artisan fetch:weather         # Snapshot cuaca real-time
php artisan fetch:exchangerates   # Kurs semua mata uang vs USD
php artisan fetch:ports           # Data pelabuhan (dengan fuzzy matching nama)
php artisan fetch:news            # Berita umum + analisis sentimen
php artisan match:news-countries  # Cocokkan berita ke negara
php artisan fetch:news-country    # Berita spesifik 60 negara GDP terbesar
php artisan calculate:risk        # Hitung skor risiko semua negara
```

> ⏱️ **Estimasi waktu:** Proses fetch keseluruhan membutuhkan waktu sekitar 10–30 menit tergantung koneksi internet dan batas rate dari masing-masing API.

### Jalankan Server

```bash
php artisan serve
```

Buka di browser: **`http://127.0.0.1:8000`**

---

## 📋 Yang Masih Perlu Dikerjakan

### Frontend
- [ ] Implementasi Bootstrap 5 untuk tampilan antarmuka
- [ ] Peta interaktif dengan Leaflet.js (visualisasi risk per negara)
- [ ] Grafik tren historis dengan Chart.js (ekonomi, cuaca, kurs, risiko)
- [ ] Halaman perbandingan antar negara (*Country Comparator*)

### Fitur Backend
- [ ] Endpoint `GET /api/currency` (terpisah, belum dibuat)
- [ ] Implementasi fitur Watchlist (tabel sudah ada, logikanya belum)
- [ ] Admin Dashboard (tabel artikel sudah ada, UI belum)

---

## 📁 Struktur Direktori

```
dashboard_internasional/
├── app/
│   ├── Console/Commands/          # Semua Artisan command fetch data
│   │   ├── FetchCountries.php
│   │   ├── FetchEconomic.php
│   │   ├── FetchCoordinates.php
│   │   ├── FetchWeather.php
│   │   ├── FetchExchangeRates.php
│   │   ├── FetchPorts.php
│   │   ├── FetchNews.php
│   │   ├── MatchNewsCountries.php
│   │   ├── FetchNewsCountry.php
│   │   └── CalculateRisk.php
│   ├── Http/Controllers/
│   │   ├── CountryController.php
│   │   ├── RiskController.php
│   │   ├── PortController.php
│   │   ├── NewsController.php
│   │   ├── CurrencyController.php
│   │   ├── WatchlistController.php
│   │   ├── AdminController.php
│   │   ├── AuthController.php
│   │   └── DashboardController.php
│   └── Models/
│       ├── Country.php
│       ├── EconomicIndicator.php
│       ├── ExchangeRate.php
│       ├── WeatherSnapshot.php
│       ├── RiskScore.php
│       ├── Port.php
│       ├── NewsCache.php
│       ├── PositiveWord.php
│       ├── NegativeWord.php
│       ├── User.php
│       ├── Watchlist.php
│       └── Article.php
├── database/
│   ├── migrations/                # 14 file migrasi
│   └── seeders/
│       ├── PositiveWordSeeder.php
│       └── NegativeWordSeeder.php
├── routes/
│   ├── api.php                    # Definisi API endpoint
│   └── web.php                    # Definisi route halaman web
├── tests/
│   └── Feature/
│       └── CurrencyApiTest.php
└── .env.example
```

---

## 🤝 Kontribusi

1. Fork repositori ini
2. Buat branch baru: `git checkout -b feature/nama-fitur`
3. Commit perubahan: `git commit -m "feat: deskripsi perubahan"`
4. Push: `git push origin feature/nama-fitur`
5. Buka Pull Request ke branch `main`

---

## 📄 Lisensi

Proyek ini dibuat sebagai **Tugas Akhir** dan dilisensikan di bawah [MIT License](LICENSE).

---

<div align="center">

**Global Logistics Dashboard**
*Supply Chain Risk Intelligence Platform*

Dibuat dengan Laravel 13 + PHP 8.5

</div>
