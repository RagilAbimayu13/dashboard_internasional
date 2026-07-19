<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;

// Main Unified Dashboard
Route::get('/', [DashboardController::class, 'index']);

// Redirect secondary pages to tabs on the main dashboard
Route::get('/risk', function () {
    return redirect('/?tab=risk');
});

Route::get('/compare', function () {
    return redirect('/?tab=compare');
});

Route::get('/currency', function () {
    return redirect('/?tab=currency');
});

Route::get('/ports', function () {
    return redirect('/?tab=ports');
});

Route::get('/weather', function () {
    return redirect('/?tab=weather');
});

Route::get('/news', function () {
    return redirect('/?tab=news');
});

// Authentication
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/watchlist', function () {
        return redirect('/?tab=watchlist');
    });
    
    // AJAX APIs for Watchlist
    Route::get('/api/watchlist', [WatchlistController::class, 'apiIndex']);
    Route::post('/api/watchlist', [WatchlistController::class, 'store']);
    Route::delete('/api/watchlist/{id}', [WatchlistController::class, 'destroy']);
});

// Admin Panel Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', function () {
        return redirect('/?tab=admin');
    });
    Route::get('/users', function () {
        return redirect('/?tab=admin');
    });
    Route::get('/articles', function () {
        return redirect('/?tab=admin');
    });
    
    // Actions
    Route::post('/users/{id}/role', [AdminController::class, 'updateRole']);
    Route::post('/articles', [AdminController::class, 'storeArticle']);
    Route::delete('/articles/{id}', [AdminController::class, 'destroyArticle']);
    Route::post('/ports', [AdminController::class, 'storePort']);
    Route::delete('/ports/{id}', [AdminController::class, 'destroyPort']);
});