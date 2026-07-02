<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EconomicIndicator extends Model
{
    protected $fillable = [
        'country_id',
        'gdp',
        'inflation_rate',
        'population',
        'exports',
        'imports',
        'recorded_at',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}