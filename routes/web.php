<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Revolution\Feedable\Core\Driver;

Route::get('/', function () {
    return view('drivers')->with([
        'drivers' => Driver::collect()->sortKeys(),
    ]);
})->name('home');
