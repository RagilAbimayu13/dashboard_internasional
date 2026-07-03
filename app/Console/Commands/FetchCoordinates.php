<?php

namespace App\Console\Commands;

use App\Models\Country;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchCoordinates extends Command
{
    protected $signature = 'fetch:coordinates';
    protected $description = 'Isi koordinat latitude/longitude tiap negara berdasarkan ibu kotanya';

    public function handle()
    {
        $countries = Country::whereNull('latitude')->get();
        $this->info("Memproses {$countries->count()} negara...");

        $bar = $this->output->createProgressBar($countries->count());
        $bar->start();

        $failed = 0;

        foreach ($countries as $country) {
            $searchTerm = $country->capital ?: $country->name;

            $coords = $this->geocode($searchTerm);

            // Kalau ibu kota tidak ketemu, coba pakai nama negara sebagai cadangan
            if ($coords === null && $country->capital) {
                $coords = $this->geocode($country->name);
            }

            if ($coords === null) {
                $failed++;
                $bar->advance();
                continue;
            }

            $country->update([
                'latitude' => $coords['latitude'],
                'longitude' => $coords['longitude'],
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Selesai. {$failed} negara tidak ditemukan koordinatnya.");

        return 0;
    }

    private function geocode(string $name)
    {
        try {
            $response = Http::timeout(15)
                ->retry(2, 500)
                ->get('https://geocoding-api.open-meteo.com/v1/search', [
                    'name' => $name,
                    'count' => 1,
                ]);

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();

            if (empty($data['results'])) {
                return null;
            }

            return [
                'latitude' => $data['results'][0]['latitude'],
                'longitude' => $data['results'][0]['longitude'],
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}