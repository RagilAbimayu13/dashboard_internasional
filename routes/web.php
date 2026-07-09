<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/risk', function () {
    return view('risk');
});

Route::get('/compare', function () {
    return view('compare');
});