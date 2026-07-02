<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['name' => 'Indonesia', 'code' => 'IDN', 'currency_code' => 'IDR', 'region' => 'Southeast Asia', 'capital' => 'Jakarta'],
            ['name' => 'Germany', 'code' => 'DEU', 'currency_code' => 'EUR', 'region' => 'Europe', 'capital' => 'Berlin'],
            ['name' => 'China', 'code' => 'CHN', 'currency_code' => 'CNY', 'region' => 'East Asia', 'capital' => 'Beijing'],
            ['name' => 'Australia', 'code' => 'AUS', 'currency_code' => 'AUD', 'region' => 'Oceania', 'capital' => 'Canberra'],
            ['name' => 'United States', 'code' => 'USA', 'currency_code' => 'USD', 'region' => 'North America', 'capital' => 'Washington D.C.'],
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }
    }
}