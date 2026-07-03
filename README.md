# Global Supply Chain Risk Intelligence Platform

Tugas akhir project - platform monitoring risiko rantai pasok global. Intinya narik data dari beberapa API (cuaca, ekonomi, kurs, berita) terus digabung jadi satu sistem yang bisa kasih skor risiko per negara buat bantu keputusan bisnis (misal mau import barang dari negara mana yang risikonya rendah).

## Stack yang dipakai

Backend: PHP 8.5 + Laravel 13 + MySQL (pake XAMPP)

Frontend belum digarap, rencananya Bootstrap 5 + Chart.js + Leaflet.js buat peta.

## Soal API - ada beberapa yang beda dari rencana awal

Waktu mulai ngerjain, ternyata REST Countries API (yang harusnya dipake buat data negara) udah **deprecated**. Sempet bingung kenapa selalu gagal fetch, ternyata providernya udah matiin versi lama dan minta API key buat versi baru. Akhirnya cari alternatif dan ketemu **countries.dev** yang masih gratis tanpa key, datanya juga setara.

Yang lain masih sesuai rencana:
- Data ekonomi (GDP, inflasi, populasi) → World Bank API
- Cuaca → Open-Meteo (plus geocoding API mereka juga buat cari koordinat negara)
- Kurs mata uang → ExchangeRate-API versi open access (open.er-api.com), gratis dan sekali call langsung dapet semua currency
- Berita → GNews, ini yang butuh daftar akun dulu (tapi tetep gratis)
- Data pelabuhan → awalnya mau pake World Port Index resmi tapi susah di-download langsung, jadi pake dataset JSON yang udah ada di GitHub (sumbernya sama, cuma udah diolah jadi format yang gampang dipake)

## Database

Ada 14 tabel. Yang penting dibedain: data yang statis (kayak nama negara, kode ISO) sama data yang berubah-ubah dari waktu ke waktu (GDP, cuaca, kurs) - yang kedua ini disimpen sebagai history, jadi tiap kali di-fetch nambah row baru, bukan nimpa yang lama. Ini penting biar nanti bisa bikin grafik trend.

Tabel-tabel utama:
- `countries` - data dasar negara
- `economic_indicators`, `weather_snapshots`, `exchange_rates` - history data yang berubah
- `risk_scores` - hasil perhitungan risk scoring
- `ports` - data pelabuhan
- `news_cache` - berita + hasil sentiment analysis
- `positive_words` / `negative_words` - dictionary buat sentiment analysis (20 kata masing-masing)
- `watchlists`, `articles` - buat fitur favorit & admin (belum diimplementasi fiturnya, tabelnya udah ada)

## Soal kelengkapan data (penting buat dijelasin kalo ditanya)

Nggak semua negara punya data lengkap di semua kategori, dan ini emang wajar - bukan bug. Beberapa negara/teritori kecil memang nggak punya data di World Bank, atau nama negaranya beda format antar dataset satu sama lain jadi susah dicocokin otomatis.

Kira-kira segini progresnya:
- Negara dasar: 250/250, lengkap semua
- Data ekonomi: sekitar 212/250 (85%)
- Koordinat: 247/250
- Cuaca: sekitar 95%
- Kurs: 244/249
- Pelabuhan: awalnya cuma 71% yang match, abis diperbaikin logic pencocokan namanya (pake alias + fuzzy matching) naik jadi 91%

Sisanya emang keterbatasan dari sumber datanya sendiri, udah dicoba beberapa kali fetch ulang juga hasilnya stabil di angka segitu.

## Command-command yang dipake

Semua data di-fetch pake custom Artisan command, bukan diinput manual (250 negara mana mungkin diketik satu-satu):

```
php artisan fetch:countries        # negara dari countries.dev
php artisan fetch:economic         # GDP, inflasi, populasi
php artisan fetch:coordinates      # lat/lng buat keperluan cuaca
php artisan fetch:weather          # cuaca real-time
php artisan fetch:exchangerates    # kurs mata uang
php artisan fetch:ports            # data pelabuhan
php artisan fetch:news             # berita umum + sentiment
php artisan match:news-countries   # cocokin berita yang udah ada ke negara
php artisan fetch:news-country     # berita spesifik buat 60 negara GDP terbesar
php artisan calculate:risk         # hitung risk score
```

Kebanyakan command ini aman dijalanin berkali-kali, bakal skip data yang udah ada (kecuali weather & exchange rate yang emang didesain buat nyimpen history, jadi refresh tiap 6 jam).

## Risk Scoring

Formula risk score ngikutin contoh yang ada di soal:

```
Total Risk = (Weather x 30%) + (Inflation x 20%) + (News Sentiment x 40%) + (Currency x 10%)
```

Tiap faktor dikonversi ke skala 0-100 dulu (100 = paling berisiko):
- Weather: berdasarkan storm_risk (low=10, medium=50, high=90)
- Inflation: linear, makin tinggi inflasinya makin gede skornya (dicap max di inflasi 20%)
- News sentiment: rasio berita negatif dibanding total berita
- Currency: dihitung dari seberapa besar perubahan kurs antar 2 data terakhir (volatilitas)

Kategori: <33 = Low, 33-65 = Medium, >65 = High.

Note: sentiment berita cuma spesifik buat 60 negara dengan GDP terbesar (biar hemat kuota API gratis yang cuma 100 request/hari), negara lain pake rata-rata sentiment global sebagai gantinya.

## Endpoint API yang udah jadi

```
GET /api/countries          -> semua negara
GET /api/countries/{id}     -> detail 1 negara + ekonomi, cuaca, kurs, risk score, berita, pelabuhan
GET /api/risk                -> ranking risk score semua negara
GET /api/ports                -> semua pelabuhan (bisa difilter ?country_id=)
GET /api/news                 -> berita (bisa difilter ?category= dan ?country_id=)
```

## Yang masih perlu dikerjain

- Frontend (belum disentuh sama sekali, masih murni backend/API)
- Visualisasi pake Chart.js
- Peta interaktif Leaflet.js
- Country comparison
- Watchlist (favorit negara)
- Admin dashboard
- Endpoint /api/currency terpisah

## Cara jalanin dari awal

```bash
composer install
cp .env.example .env
# isi DB_DATABASE, DB_USERNAME, DB_PASSWORD di .env
# tambahin GNEWS_API_KEY=xxx (daftar gratis di gnews.io)

php artisan key:generate
php artisan migrate
php artisan db:seed --class=PositiveWordSeeder
php artisan db:seed --class=NegativeWordSeeder

php artisan fetch:countries
php artisan fetch:economic
php artisan fetch:coordinates
php artisan fetch:weather
php artisan fetch:exchangerates
php artisan fetch:ports
php artisan fetch:news
php artisan match:news-countries
php artisan fetch:news-country
php artisan calculate:risk

php artisan serve
```

Buka di `http://127.0.0.1:8000`
