<?php

namespace App\Console\Commands;

use App\Models\NewsCache;
use App\Models\PositiveWord;
use App\Models\NegativeWord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchNews extends Command
{
    protected $signature = 'fetch:news';
    protected $description = 'Fetch berita dari GNews API dan hitung sentiment-nya';

    public function handle()
    {
        $apiKey = config('services.gnews.key');

        if (empty($apiKey)) {
            $this->error('GNEWS_API_KEY belum diset di .env');
            return 1;
        }

        $positiveWords = PositiveWord::pluck('word')->map(fn ($w) => strtolower($w))->toArray();
        $negativeWords = NegativeWord::pluck('word')->map(fn ($w) => strtolower($w))->toArray();

        $keywords = ['trade', 'shipping', 'logistics', 'supply chain'];
        $totalSaved = 0;

        foreach ($keywords as $keyword) {
            $this->info("Mengambil berita untuk kata kunci: {$keyword}...");

            try {
                $response = Http::timeout(30)
                    ->retry(3, 1000)
                    ->get('https://gnews.io/api/v4/search', [
                        'q' => $keyword,
                        'lang' => 'en',
                        'max' => 10,
                        'apikey' => $apiKey,
                    ]);
            } catch (\Exception $e) {
                $this->warn("Gagal fetch untuk '{$keyword}' setelah beberapa percobaan. Melanjutkan ke keyword berikutnya.");
                continue;
            }

            if (! $response->successful()) {
                $this->warn("Gagal fetch untuk '{$keyword}'. Status: " . $response->status());
                continue;
            }

            $articles = $response->json()['articles'] ?? [];

            foreach ($articles as $article) {
                $title = $article['title'] ?? '';

                if (empty($title)) {
                    continue;
                }

                [$positiveScore, $negativeScore, $sentiment] = $this->analyzeSentiment(
                    $title,
                    $positiveWords,
                    $negativeWords
                );

                NewsCache::updateOrCreate(
                    ['source_url' => $article['url']],
                    [
                        'title' => $title,
                        'category' => $keyword,
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

            $this->info("  → {$keyword}: " . count($articles) . " artikel diproses.");
        }

        $this->info("Selesai! {$totalSaved} berita tersimpan dengan sentiment analysis.");
        return 0;
    }

    private function analyzeSentiment(string $text, array $positiveWords, array $negativeWords): array
    {
        $words = str_word_count(strtolower($text), 1);

        $positiveScore = 0;
        $negativeScore = 0;

        foreach ($words as $word) {
            if (in_array($word, $positiveWords)) {
                $positiveScore++;
            }
            if (in_array($word, $negativeWords)) {
                $negativeScore++;
            }
        }

        if ($positiveScore > $negativeScore) {
            $sentiment = 'positive';
        } elseif ($negativeScore > $positiveScore) {
            $sentiment = 'negative';
        } else {
            $sentiment = 'neutral';
        }

        return [$positiveScore, $negativeScore, $sentiment];
    }
}