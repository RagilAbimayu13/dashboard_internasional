<?php

namespace Database\Seeders;

use App\Models\NegativeWord;
use Illuminate\Database\Seeder;

class NegativeWordSeeder extends Seeder
{
    public function run(): void
    {
        $words = [
            'war', 'crisis', 'inflation', 'delay', 'disaster',
            'decline', 'shortage', 'conflict', 'recession', 'collapse',
            'disruption', 'sanction', 'tariff', 'deficit', 'unrest',
            'shutdown', 'congestion', 'strike', 'volatile', 'risk',
        ];

        foreach ($words as $word) {
            NegativeWord::firstOrCreate(['word' => $word]);
        }
    }
}