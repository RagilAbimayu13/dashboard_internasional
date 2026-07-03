<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\NewsCache;
use Illuminate\Console\Command;

class MatchNewsToCountries extends Command
{
    protected $signature = 'match:news-countries';
    protected $description = 'Cocokkan berita yang sudah ada dengan negara berdasarkan judul';

    public function handle()
    {
        $countries = Country::all();
        $newsList = NewsCache::whereNull('country_id')->get();

        $this->info("Memeriksa {$newsList->count()} berita...");
        $matched = 0;

        foreach ($newsList as $news) {
            foreach ($countries as $country) {
                if (str_contains(strtolower($news->title), strtolower($country->name))) {
                    $news->update(['country_id' => $country->id]);
                    $matched++;
                    break;
                }
            }
        }

        $this->info("Selesai! {$matched} berita berhasil dicocokkan ke negara tertentu.");
        return 0;
    }
}