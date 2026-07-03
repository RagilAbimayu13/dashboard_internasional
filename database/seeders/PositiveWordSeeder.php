<?php

namespace Database\Seeders;

use App\Models\PositiveWord;
use Illuminate\Database\Seeder;

class PositiveWordSeeder extends Seeder
{
    public function run(): void
    {
        $words = [
            'growth', 'increase', 'profit', 'stable', 'improve',
            'recovery', 'surplus', 'boost', 'expand', 'gain',
            'rise', 'positive', 'strong', 'success', 'agreement',
            'partnership', 'investment', 'progress', 'efficient', 'resilient',
        ];

        foreach ($words as $word) {
            PositiveWord::firstOrCreate(['word' => $word]);
        }
    }
}