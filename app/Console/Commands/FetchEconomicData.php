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
        $skippedComplete = 0;
        $updated = 0;

        foreach ($countries as $country) {
            $existing = EconomicIndicator::where('country_id', $country->id)->first();

            // Skip HANYA kalau data yang sudah ada benar-benar lengkap (GDP, inflasi, populasi semuanya terisi)
            if ($existing && $existing->gdp !== null && $existing->inflation_rate !== null && $existing->population !== null) {
                $skippedComplete++;
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

            if ($existing) {
                // Update baris yang sudah ada, isi field yang sebelumnya kosong
                $existing->update([
                    'gdp' => $gdp ?? $existing->gdp,
                    'inflation_rate' => $inflation ?? $existing->inflation_rate,
                    'population' => $population ?? $existing->population,
                ]);
                $updated++;
            } else {
                EconomicIndicator::create([
                    'country_id' => $country->id,
                    'gdp' => $gdp,
                    'inflation_rate' => $inflation,
                    'population' => $population,
                    'recorded_at' => now(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Selesai. {$skippedComplete} sudah lengkap, {$updated} diperbarui, {$failed} tidak ada data sama sekali.");

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