<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NegativeWord extends Model
{
    public $timestamps = false;

    protected $fillable = ['word'];
}