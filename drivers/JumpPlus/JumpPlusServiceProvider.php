<?php

declare(strict_types=1);

namespace Revolution\Feedable\JumpPlus;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class JumpPlusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::prefix('shonenjumpplus')->group(function () {
            Route::get('daily', JumpPlusAction::class);
        });
    }
}
