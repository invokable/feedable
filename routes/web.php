<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Revolution\Feedable\Core\Driver;
use Revolution\Feedable\Drivers\Note\NoteIndexDriver;

Route::get('/', function () {
    return view('drivers')->with([
        'drivers' => Driver::collect()->sortKeys(),
    ]);
})->name('home');

Route::get('note/test', function () {
    $items = new NoteIndexDriver()->handle();
    return 'Successfully fetched '.count($items).' items.';
});
