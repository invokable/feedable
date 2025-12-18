<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Revolution\Feedable\Core\Driver;

// Route::get('/user', function (Request $request) {
//    return $request->user();
// })->middleware('auth:sanctum');

Route::get('drivers', function () {
    return response(Driver::toPrettyJson())
        ->header('Content-Type', 'application/json');
});
