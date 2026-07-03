<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\WeatherSnapshot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchWeatherData extends Command
{
    protected $signature = 'fetch:weather';
    protected $description = 'Fetch data cuaca terkini tiap negara dari Open-Meteo API';

    public function handle()
    {
        $countries = Country::whereNotNull('latitude')->get();
        $this->info("Memproses {$countries->count()} negara...");

        $bar = $this->output->createProgressBar($countries->count());
        $bar->start();

        $failed = 0;

        foreach ($countries as $country) {
            $weather = $this->fetchWeather($country->latitude, $country->longitude);

            if ($weather === null) {
                $failed++;
                $bar->advance();
                continue;
            }

            WeatherSnapshot::create([
                'country_id' => $country->id,
                'temperature' => $weather['temperature'],
                'rainfall' => $weather['rainfall'],
                'wind_speed' => $weather['wind_speed'],
                'storm_risk' => $this->calculateStormRisk($weather['wind_speed'], $weather['rainfall']),
                'recorded_at' => now(),
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Selesai. {$failed} negara gagal diambil datanya.");

        return 0;
    }

    private function fetchWeather(float $lat, float $lng)
    {
        try {
            $response = Http::timeout(15)
                ->retry(2, 500)
                ->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'current' => 'temperature_2m,precipitation,wind_speed_10m',
                ]);

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();

            if (empty($data['current'])) {
                return null;
            }

            return [
                'temperature' => $data['current']['temperature_2m'] ?? null,
                'rainfall' => $data['current']['precipitation'] ?? null,
                'wind_speed' => $data['current']['wind_speed_10m'] ?? null,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    // Contoh algoritma sederhana untuk menentukan risiko badai
    // Silakan modifikasi logika ini sesuai interpretasi Anda sendiri
    private function calculateStormRisk(?float $windSpeed, ?float $rainfall): string
    {
        if ($windSpeed === null || $rainfall === null) {
            return 'unknown';
        }

        if ($windSpeed > 60 || $rainfall > 20) {
            return 'high';
        }

        if ($windSpeed > 30 || $rainfall > 10) {
            return 'medium';
        }

        return 'low';
    }
}