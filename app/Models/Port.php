<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    protected $fillable = [
        'name',
        'country_id',
        'latitude',
        'longitude',
        'port_type',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}