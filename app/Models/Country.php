<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'name',
        'code',
        'currency_code',
        'region',
        'capital',
        'flag_url',
        'latitude',
        'longitude',
    ];

    // Relasi: satu country punya banyak economic indicators
    public function economicIndicators()
    {
        return $this->hasMany(EconomicIndicator::class);
    }

    // Relasi: satu country punya banyak exchange rates
    public function exchangeRates()
    {
        return $this->hasMany(ExchangeRate::class);
    }

    // Relasi: satu country punya banyak weather snapshots
    public function weatherSnapshots()
    {
        return $this->hasMany(WeatherSnapshot::class);
    }

    // Relasi: satu country punya banyak risk scores
    public function riskScores()
    {
        return $this->hasMany(RiskScore::class);
    }

    // Relasi: satu country punya banyak ports
    public function ports()
    {
        return $this->hasMany(Port::class);
    }

    // Relasi: satu country punya banyak berita terkait
    public function news()
    {
        return $this->hasMany(NewsCache::class);
    }

    // Relasi: satu country bisa masuk watchlist banyak user
    public function watchlists()
    {
        return $this->hasMany(Watchlist::class);
    }
}