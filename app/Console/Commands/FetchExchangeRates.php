<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\ExchangeRate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchExchangeRates extends Command
{
    protected $signature = 'fetch:exchangerates';
    protected $description = 'Fetch kurs mata uang dari ExchangeRate-API (open access)';

    public function handle()
    {
        $this->info('Mengambil data kurs dari open.er-api.com...');

        $response = Http::timeout(15)->get('https://open.er-api.com/v6/latest/USD');

        if (! $response->successful()) {
            $this->error('Gagal mengambil data kurs. Status: ' . $response->status());
            return 1;
        }

        $data = $response->json();

        if (($data['result'] ?? null) !== 'success' || empty($data['rates'])) {
            $this->error('Response API tidak valid.');
            return 1;
        }

        $rates = $data['rates'];
        $countries = Country::whereNotNull('currency_code')->get();

        $this->info("Memproses {$countries->count()} negara...");
        $bar = $this->output->createProgressBar($countries->count());
        $bar->start();

        $count = 0;
        $notFound = 0;

        foreach ($countries as $country) {
            $rate = $rates[$country->currency_code] ?? null;

            if ($rate === null) {
                $notFound++;
                $bar->advance();
                continue;
            }

            ExchangeRate::create([
                'country_id' => $country->id,
                'currency_code' => $country->currency_code,
                'rate_to_usd' => $rate,
                'recorded_at' => now(),
            ]);

            $count++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Selesai! {$count} kurs tersimpan, {$notFound} mata uang tidak ditemukan.");

        return 0;
    }
}