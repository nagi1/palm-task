<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ScrapeController;
use Illuminate\Support\Facades\Route;

// Public endpoint returning stored products
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Protected scrape endpoint (Sanctum token required)
Route::post('/scrape', [ScrapeController::class, 'store'])->middleware('auth:sanctum');
