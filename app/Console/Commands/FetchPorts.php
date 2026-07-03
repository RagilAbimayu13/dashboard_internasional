<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\Port;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchPorts extends Command
{
    protected $signature = 'fetch:ports';
    protected $description = 'Import data pelabuhan dari World Port Index dataset';

    // Alias untuk variasi nama negara yang umum berbeda antar dataset
    private array $countryAliases = [
        'united states' => 'united states of america',
        'usa' => 'united states of america',
        'uk' => 'united kingdom',
        'south korea' => 'korea, republic of',
        'north korea' => "korea, democratic people's republic of",
        'russia' => 'russian federation',
        'vietnam' => 'viet nam',
        'ivory coast' => "cote d'ivoire",
        'syria' => 'syrian arab republic',
        'laos' => "lao people's democratic republic",
        'iran' => 'iran, islamic republic of',
        'venezuela' => 'venezuela, bolivarian republic of',
        'tanzania' => 'tanzania, united republic of',
        'bolivia' => 'bolivia, plurinational state of',
        'moldova' => 'moldova, republic of',
        'brunei' => 'brunei darussalam',
        'micronesia' => 'micronesia, federated states of',
        'macau' => 'macao',
        'burma' => 'myanmar',
        'cape verde' => 'cabo verde',
        'swaziland' => 'eswatini',
    ];

    public function handle()
    {
        $this->info('Mengunduh dataset World Port Index...');

        $response = Http::timeout(30)->get('https://raw.githubusercontent.com/tayljordan/ports/main/ports.json');

        if (! $response->successful()) {
            $this->error('Gagal mengunduh dataset. Status: ' . $response->status());
            return 1;
        }

        $data = $response->json();
        $ports = $data['ports'] ?? [];

        $this->info(count($ports) . ' pelabuhan ditemukan di dataset.');

        $countries = Country::all();
        $countryMap = $countries->mapWithKeys(fn ($c) => [strtolower($c->name) => $c->id]);
        $countryNames = $countries->pluck('name', 'id');

        $bar = $this->output->createProgressBar(count($ports));
        $bar->start();

        $saved = 0;
        $skipped = 0;

        foreach ($ports as $item) {
            $rawName = strtolower(trim($item['country'] ?? ''));
            $countryId = $this->matchCountry($rawName, $countryMap, $countryNames);

            $portName = $item['wpi_port_name'] ?? $item['point_of_interest'] ?? null;
            $lat = $item['latitude'] ?? null;
            $lng = $item['longitude'] ?? null;

            if ($countryId === null || empty($portName) || $lat === null || $lng === null) {
                $skipped++;
                $bar->advance();
                continue;
            }

            Port::updateOrCreate(
                ['name' => $portName, 'country_id' => $countryId],
                ['latitude' => $lat, 'longitude' => $lng, 'port_type' => $item['port_size'] ?? null]
            );

            $saved++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Selesai! {$saved} pelabuhan tersimpan, {$skipped} dilewati.");

        return 0;
    }

    private function matchCountry(string $rawName, $countryMap, $countryNames): ?int
    {
        if (empty($rawName)) {
            return null;
        }

        if (isset($countryMap[$rawName])) {
            return $countryMap[$rawName];
        }

        if (isset($this->countryAliases[$rawName]) && isset($countryMap[$this->countryAliases[$rawName]])) {
            return $countryMap[$this->countryAliases[$rawName]];
        }

        foreach ($countryMap as $name => $id) {
            if (str_contains($name, $rawName) || str_contains($rawName, $name)) {
                return $id;
            }
        }

        $bestScore = 0;
        $bestId = null;

        foreach ($countryNames as $id => $name) {
            similar_text($rawName, strtolower($name), $percent);
            if ($percent > $bestScore && $percent >= 85) {
                $bestScore = $percent;
                $bestId = $id;
            }
        }

        return $bestId;
    }
}