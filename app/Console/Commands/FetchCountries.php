<?php

namespace App\Console\Commands;

use App\Models\Country;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchCountries extends Command
{
    protected $signature = 'fetch:countries';
    protected $description = 'Fetch semua negara dari countries.dev API dan simpan ke database';

    public function handle()
    {
        $this->info('Mengambil data negara dari countries.dev...');

        $response = Http::get('https://countries.dev/countries');

        if (! $response->successful()) {
            $this->error('Gagal mengambil data. Status: ' . $response->status());
            return 1;
        }

        $countries = $response->json();
        $count = 0;
        $skipped = 0;

        foreach ($countries as $item) {
            $code = $item['alpha3Code'] ?? null;

            if (empty($code)) {
                $skipped++;
                continue;
            }

            $currencyCode = null;
            if (! empty($item['currencies'][0]['code'])) {
                $currencyCode = $item['currencies'][0]['code'];
            }

            Country::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $item['name'] ?? 'Unknown',
                    'currency_code' => $currencyCode,
                    'region' => $item['region'] ?? null,
                    'capital' => $item['capital'] ?? null,
                    'flag_url' => $item['flags']['png'] ?? null,
                ]
            );

            $count++;
        }

        $this->info("Selesai! {$count} negara berhasil disimpan/diperbarui. ({$skipped} dilewati karena data tidak lengkap)");
        return 0;
    }
}