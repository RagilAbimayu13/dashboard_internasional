<?php

namespace Tests\Feature;

use Tests\TestCase;

class CurrencyApiTest extends TestCase
{
    public function test_currency_api_returns_latest_exchange_rates(): void
    {
        // Act: Request the latest rates
        $response = $this->getJson('/api/currency');

        // Assert: Verify successful response and check structure
        $response->assertStatus(200);
        
        // Ensure it returns a list of rates containing currency_code and rate_to_usd
        $response->assertJsonStructure([
            '*' => [
                'id',
                'country_id',
                'currency_code',
                'rate_to_usd',
                'recorded_at',
                'country' => [
                    'id',
                    'name',
                    'code',
                    'currency_code'
                ]
            ]
        ]);
    }

    public function test_currency_api_returns_historical_rates_when_requested(): void
    {
        // Act: Request historical rates for a country present in the database
        $listResponse = $this->getJson('/api/currency');
        $list = $listResponse->json();
        
        if (!empty($list)) {
            $firstRate = $list[0];
            $countryId = $firstRate['country_id'];
            
            $response = $this->getJson("/api/currency?country_id={$countryId}&history=true");
            $response->assertStatus(200);
            
            // Check that it's a list and contains rate_to_usd
            $response->assertJsonStructure([
                '*' => [
                    'id',
                    'country_id',
                    'currency_code',
                    'rate_to_usd',
                    'recorded_at'
                ]
            ]);
        } else {
            $this->markTestSkipped('No exchange rates found in database to test history.');
        }
    }
}
