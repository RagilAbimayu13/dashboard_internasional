<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\EconomicIndicator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchEconomicData extends Command
{
    protected $signature = 'fetch:economic';
    protected $description = 'Fetch data GDP, inflasi, dan populasi dari World Bank API';

    public function handle()
    {
        $countries = Country::all();
        $this->info("Memproses {$countries->count()} negara...");

        $bar = $this->output->createProgressBar($countries->count());
        $bar->start();

        $failed = 0;
        $skippedExisting = 0;

        foreach ($countries as $country) {
            if (EconomicIndicator::where('country_id', $country->id)->exists()) {
                $skippedExisting++;
                $bar->advance();
                continue;
            }

            $gdp = $this->fetchIndicator($country->code, 'NY.GDP.MKTP.CD');
            $inflation = $this->fetchIndicator($country->code, 'FP.CPI.TOTL.ZG');
            $population = $this->fetchIndicator($country->code, 'SP.POP.TOTL');

            if ($gdp === null && $inflation === null && $population === null) {
                $failed++;
                $bar->advance();
                continue;
            }

            EconomicIndicator::create([
                'country_id' => $country->id,
                'gdp' => $gdp,
                'inflation_rate' => $inflation,
                'population' => $population,
                'recorded_at' => now(),
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Selesai. {$skippedExisting} sudah ada sebelumnya, {$failed} tidak ada data.");

        return 0;
    }

    private function fetchIndicator(string $countryCode, string $indicatorCode)
    {
        try {
            $response = Http::timeout(20)
                ->retry(3, 1000)
                ->get("https://api.worldbank.org/v2/country/{$countryCode}/indicator/{$indicatorCode}", [
                    'format' => 'json',
                    'per_page' => 1,
                    'mrnev' => 1,
                ]);

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();

            return $data[1][0]['value'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}