<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/reorder-suggestions', [ProductController::class, 'getReorderSuggestions']);

// Test route
Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});