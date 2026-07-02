<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskScore extends Model
{
    protected $fillable = [
        'country_id',
        'weather_score',
        'inflation_score',
        'currency_score',
        'news_sentiment_score',
        'total_score',
        'risk_level',
        'calculated_at',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}