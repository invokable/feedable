<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Revolution\Feedable\Core\Driver;

// 仮でサイトリストを表示
Route::get('/', function () {
    return view('drivers')->with([
        'drivers' => Driver::collect()->sortKeys(),
    ]);
})->name('home');
