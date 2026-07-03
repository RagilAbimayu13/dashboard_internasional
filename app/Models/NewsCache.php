<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsCache extends Model
{
    protected $table = 'news_cache';

    protected $fillable = [
        'title',
        'source_url',
        'country_id',
        'category',
        'sentiment',
        'positive_score',
        'negative_score',
        'published_at',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}