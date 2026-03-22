<?php

use Illuminate\Support\Facades\Route;

// Catch-all route: serve the Vue SPA for every non-API request
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
