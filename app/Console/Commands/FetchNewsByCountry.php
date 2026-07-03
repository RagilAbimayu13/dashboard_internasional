<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\NewsCache;
use App\Models\PositiveWord;
use App\Models\NegativeWord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchNewsByCountry extends Command
{
    protected $signature = 'fetch:news-country';
    protected $description = 'Fetch berita spesifik untuk 60 negara dengan GDP terbesar';

    public function handle()
    {
        $apiKey = config('services.gnews.key');

        if (empty($apiKey)) {
            $this->error('GNEWS_API_KEY belum diset di .env');
            return 1;
        }

        // Pilih otomatis 60 negara dengan GDP terbesar (data ekonomi terbaru per negara)
        $countries = Country::whereHas('economicIndicators')
            ->with(['economicIndicators' => fn ($q) => $q->latest('recorded_at')->limit(1)])
            ->get()
            ->sortByDesc(fn ($c) => $c->economicIndicators->first()->gdp ?? 0)
            ->take(60);

        $this->info("Memproses {$countries->count()} negara dengan GDP terbesar...");

        $positiveWords = PositiveWord::pluck('word')->map(fn ($w) => strtolower($w))->toArray();
        $negativeWords = NegativeWord::pluck('word')->map(fn ($w) => strtolower($w))->toArray();

        $bar = $this->output->createProgressBar($countries->count());
        $bar->start();

        $totalSaved = 0;
        $failed = 0;

        foreach ($countries as $country) {
            try {
                $response = Http::timeout(30)
                    ->retry(3, 1000)
                    ->get('https://gnews.io/api/v4/search', [
                        'q' => "{$country->name} trade OR economy OR export",
                        'lang' => 'en',
                        'max' => 10,
                        'apikey' => $apiKey,
                    ]);
            } catch (\Exception $e) {
                $failed++;
                $bar->advance();
                continue;
            }

            if (! $response->successful()) {
                $failed++;
                $bar->advance();
                continue;
            }

            $articles = $response->json()['articles'] ?? [];

            foreach ($articles as $article) {
                $title = $article['title'] ?? '';

                if (empty($title)) {
                    continue;
                }

                [$positiveScore, $negativeScore, $sentiment] = $this->analyzeSentiment($title, $positiveWords, $negativeWords);

                NewsCache::updateOrCreate(
                    ['source_url' => $article['url']],
                    [
                        'title' => $title,
                        'country_id' => $country->id,
                        'category' => 'economy',
                        'sentiment' => $sentiment,
                        'positive_score' => $positiveScore,
                        'negative_score' => $negativeScore,
                        'published_at' => ! empty($article['publishedAt'])
                            ? \Carbon\Carbon::parse($article['publishedAt'])
                            : now(),
                    ]
                );

                $totalSaved++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Selesai! {$totalSaved} berita tersimpan, {$failed} negara gagal di-fetch.");

        return 0;
    }

    private function analyzeSentiment(string $text, array $positiveWords, array $negativeWords): array
    {
        $words = str_word_count(strtolower($text), 1);
        $positiveScore = 0;
        $negativeScore = 0;

        foreach ($words as $word) {
            if (in_array($word, $positiveWords)) $positiveScore++;
            if (in_array($word, $negativeWords)) $negativeScore++;
        }

        $sentiment = $positiveScore > $negativeScore ? 'positive' : ($negativeScore > $positiveScore ? 'negative' : 'neutral');

        return [$positiveScore, $negativeScore, $sentiment];
    }
}