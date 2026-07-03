# Global Supply Chain Risk Intelligence Platform

Platform monitoring risiko rantai pasok global berbasis integrasi multi-API dan analitik data. Dibangun dengan Laravel, mengombinasikan data cuaca, ekonomi, kurs mata uang, dan (segera) berita untuk membantu pengambilan keputusan bisnis terkait risiko impor/ekspor antar negara.

## Tech Stack

**Backend**
- PHP 8.5
- Laravel 13
- MySQL (via XAMPP)

**Frontend** *(belum dikerjakan)*
- Bootstrap 5
- AJAX / JavaScript ES6

**Visualisasi** *(belum dikerjakan)*
- Chart.js
- Leaflet.js

## Status API Eksternal

Beberapa API yang tertulis di spesifikasi awal sudah berubah/deprecated sejak deskripsi tugas dibuat. Berikut API yang benar-benar dipakai di project ini beserta penggantinya:

| Kebutuhan Data | API yang Dipakai | Keterangan |
|---|---|---|
| Data negara (nama, kode, currency, region, capital, flag) | [countries.dev](https://countries.dev) | Pengganti REST Countries v3.1 yang sudah deprecated. Gratis, tanpa API key. |
| GDP, inflasi, populasi | [World Bank API](https://api.worldbank.org) | Sesuai spek awal. Gratis, tanpa API key. |
| Koordinat negara (lat/lng) | [Open-Meteo Geocoding API](https://geocoding-api.open-meteo.com) | Gratis, tanpa API key. Dibutuhkan sebagai prasyarat untuk fetch cuaca. |
| Cuaca real-time | [Open-Meteo Forecast API](https://api.open-meteo.com) | Sesuai spek awal. Gratis, tanpa API key, hingga 10.000 request/hari. |
| Kurs mata uang | [ExchangeRate-API (open access)](https://open.er-api.com) | Sesuai spek awal (provider sama). Gratis, tanpa API key. 1 request mengambil kurs semua mata uang sekaligus. |
| Berita ekonomi/logistik/geopolitik | GNews API | *Belum diimplementasikan.* Butuh signup akun gratis (bukan komersial, limit 100 request/hari). |
| Pelabuhan dunia | World Port Index Dataset | *Belum diimplementasikan.* |
| Peta dunia | OpenStreetMap + Leaflet.js | *Belum diimplementasikan.* |

## Skema Database

14 tabel, dirancang dengan pemisahan data statis vs data historis (time-series) supaya mendukung fitur grafik trend di spek.

**Data statis**
- `countries` — nama, kode ISO, currency, region, capital, koordinat, flag

**Data historis** *(1 negara bisa punya banyak baris, dicatat per waktu fetch)*
- `economic_indicators` — GDP, inflasi, populasi, ekspor, impor
- `weather_snapshots` — temperatur, curah hujan, kecepatan angin, storm risk
- `exchange_rates` — kurs terhadap USD
- `risk_scores` — hasil Risk Scoring Engine *(belum diimplementasikan)*

**Fitur lain**
- `ports` — data pelabuhan *(tabel sudah ada, data belum diisi)*
- `news_cache` — cache berita + hasil sentiment analysis *(tabel sudah ada, data belum diisi)*
- `watchlists` — negara favorit yang dipantau user
- `articles` — artikel analisis buatan admin
- `positive_words` / `negative_words` — dictionary untuk sentiment analysis *(tabel sudah ada, data belum diisi)*

**Bawaan Laravel**
- `users`, `cache`, `jobs`, `personal_access_tokens`

## Artisan Commands

Command custom untuk fetch data dari API eksternal. Semua command aman dijalankan berulang kali (idempotent — otomatis skip data yang sudah ada, kecuali dinyatakan lain).

```bash
# Fetch 250 negara dari countries.dev
php artisan fetch:countries

# Fetch GDP, inflasi, populasi dari World Bank (skip negara yang sudah punya data)
php artisan fetch:economic

# Fetch koordinat lat/lng negara (skip negara yang sudah punya koordinat)
php artisan fetch:coordinates

# Fetch cuaca real-time (SELALU insert baris baru — untuk data historis/trend)
php artisan fetch:weather

# Fetch kurs semua mata uang (1 API call untuk semua negara)
php artisan fetch:exchangerates
```

## API Endpoints

| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/api/countries` | Daftar semua negara |
| GET | `/api/countries/{id}` | Detail 1 negara + data ekonomi, cuaca, kurs terbaru |

*Endpoint lain (`/api/risk`, `/api/ports`, `/api/news`, `/api/currency`) sesuai spek — belum diimplementasikan.*

## Progress Checklist

- [x] Environment setup (PHP, Composer, MySQL, Laravel)
- [x] Skema database (14 tabel + migration)
- [x] Model Eloquent dengan relasi
- [x] Integrasi countries.dev (250 negara)
- [x] Integrasi World Bank API (189 negara dengan data ekonomi)
- [x] Integrasi Open-Meteo Geocoding (244 negara dengan koordinat)
- [x] Integrasi Open-Meteo Forecast (233 negara dengan data cuaca)
- [x] Integrasi ExchangeRate-API (244 negara dengan kurs)
- [x] Endpoint `/api/countries` dan `/api/countries/{id}`
- [ ] Integrasi GNews API
- [ ] Sentiment Analysis (lexicon-based, PHP)
- [ ] Risk Scoring Engine
- [ ] Data pelabuhan (World Port Index)
- [ ] Endpoint API sisanya (`/api/risk`, `/api/ports`, `/api/news`, `/api/currency`)
- [ ] Frontend (Bootstrap, AJAX)
- [ ] Visualisasi (Chart.js — GDP/Inflation/Currency/Risk Trend)
- [ ] Peta interaktif (Leaflet.js — Global Weather Monitoring, Port Location Dashboard)
- [ ] Country Comparison Engine
- [ ] Favorite Monitoring List (watchlist)
- [ ] Admin Dashboard

## Instalasi (Setup dari Nol)

```bash
# 1. Clone/masuk ke folder project
cd dashboard_internasional

# 2. Install dependency
composer install

# 3. Copy .env dan sesuaikan koneksi database
cp .env.example .env
# edit DB_DATABASE, DB_USERNAME, DB_PASSWORD sesuai environment lokal

# 4. Generate application key
php artisan key:generate

# 5. Jalankan migration
php artisan migrate

# 6. Fetch data awal (berurutan, karena beberapa bergantung pada tabel countries)
php artisan fetch:countries
php artisan fetch:economic
php artisan fetch:coordinates
php artisan fetch:weather
php artisan fetch:exchangerates

# 7. Jalankan server
php artisan serve
```

Akses di `http://127.0.0.1:8000`.

## Catatan Pengembangan

Project ini dikerjakan sebagai tugas akhir mata kuliah, dengan interpretasi dan algoritma (khususnya Risk Scoring Engine dan Sentiment Analysis) dirancang secara mandiri sesuai ketentuan dosen bahwa tiap mahasiswa harus memiliki pendekatan yang berbeda.
