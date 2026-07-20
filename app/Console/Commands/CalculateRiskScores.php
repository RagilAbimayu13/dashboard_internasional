<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\NewsCache;
use App\Models\RiskScore;
use Illuminate\Console\Command;

class CalculateRiskScores extends Command
{
    protected $signature = 'calculate:risk';
    protected $description = 'Hitung Risk Score tiap negara berdasarkan cuaca, inflasi, kurs, dan sentiment berita';

    // Bobot sesuai contoh di spesifikasi dosen — silakan ubah sesuai interpretasi Anda
    private const WEIGHT_WEATHER = 0.30;
    private const WEIGHT_INFLATION = 0.20;
    private const WEIGHT_NEWS = 0.40;
    private const WEIGHT_CURRENCY = 0.10;

    public function handle()
    {
        // Fallback global, dipakai kalau suatu negara tidak punya berita spesifik
        $globalNewsRisk = $this->calculateNewsRisk(null);
        $this->info("Skor risiko berita global (fallback): {$globalNewsRisk}/100");

        $countries = Country::with([
            'economicIndicators' => fn ($q) => $q->latest('recorded_at')->limit(1),
            'weatherSnapshots' => fn ($q) => $q->latest('recorded_at')->limit(1),
            'exchangeRates' => fn ($q) => $q->latest('recorded_at')->limit(2),
        ])->get();

        $bar = $this->output->createProgressBar($countries->count());
        $bar->start();

        $processed = 0;
        $usedSpecificNews = 0;

        foreach ($countries as $country) {
            $weatherScore = $this->calculateWeatherRisk($country->weatherSnapshots->first());
            $inflationScore = $this->calculateInflationRisk($country->economicIndicators->first());
            $currencyScore = $this->calculateCurrencyRisk($country->exchangeRates);

            // Coba cari sentiment spesifik untuk negara ini dulu
            $newsScore = $this->calculateNewsRisk($country->id);

            if ($newsScore !== null) {
                $usedSpecificNews++;
            } else {
                $newsScore = $globalNewsRisk;
            }

            if ($weatherScore === null && $inflationScore === null && $currencyScore === null) {
                $bar->advance();
                continue;
            }

            $weatherScore ??= 50;
            $inflationScore ??= 50;
            $currencyScore ??= 50;

            $totalScore =
                ($weatherScore * self::WEIGHT_WEATHER) +
                ($inflationScore * self::WEIGHT_INFLATION) +
                ($newsScore * self::WEIGHT_NEWS) +
                ($currencyScore * self::WEIGHT_CURRENCY);

            $totalScore = round($totalScore, 2);

            RiskScore::create([
                'country_id' => $country->id,
                'weather_score' => $weatherScore,
                'inflation_score' => $inflationScore,
                'currency_score' => $currencyScore,
                'news_sentiment_score' => $newsScore,
                'total_score' => $totalScore,
                'risk_level' => $this->classifyRiskLevel($totalScore),
                'calculated_at' => now(),
            ]);

            $processed++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Selesai! {$processed} negara dihitung, {$usedSpecificNews} di antaranya pakai sentiment berita spesifik negaranya sendiri.");

        return 0;
    }

    private function calculateWeatherRisk($weather): ?float
    {
        if (! $weather) {
            return null;
        }

        return match ($weather->storm_risk) {
            'low' => 10,
            'medium' => 50,
            'high' => 90,
            default => 50,
        };
    }

    private function calculateInflationRisk($economic): ?float
    {
        if (! $economic || $economic->inflation_rate === null) {
            return null;
        }

        $score = ($economic->inflation_rate / 20) * 100;

        return max(0, min(100, $score));
    }

    private function calculateCurrencyRisk($exchangeRates): ?float
    {
        if ($exchangeRates->count() < 2) {
            // Gunakan skor 50 (netral) sebagai fallback jika data histori belum cukup
            return $exchangeRates->count() === 1 ? 50 : null;
        }

        $latest = $exchangeRates->first()->rate_to_usd;
        $previous = $exchangeRates->last()->rate_to_usd;

        if ($previous == 0) {
            return 50;
        }

        $percentChange = abs(($latest - $previous) / $previous) * 100;
        $score = ($percentChange / 5) * 100;

        return max(0, min(100, $score));
    }

    // Kalau $countryId diisi, hitung sentiment KHUSUS negara itu.
    // Kalau $countryId null, hitung sentiment GLOBAL (semua berita tanpa country_id).
    private function calculateNewsRisk(?int $countryId): ?float
    {
        $query = NewsCache::query();

        if ($countryId !== null) {
            $query->where('country_id', $countryId);
        } else {
            $query->whereNull('country_id');
        }

        $totalPositive = (clone $query)->sum('positive_score');
        $totalNegative = (clone $query)->sum('negative_score');
        $total = $totalPositive + $totalNegative;

        if ($total === 0) {
            return $countryId !== null ? null : 50; // negara spesifik: null (belum ada data), global: netral 50
        }

        return round(($totalNegative / $total) * 100, 2);
    }

    private function classifyRiskLevel(float $score): string
    {
        if ($score < 33) {
            return 'Low';
        }

        if ($score < 66) {
            return 'Medium';
        }

        return 'High';
    }
}