<?php

declare(strict_types=1);

namespace Revolution\Feedable\Famitsu;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Revolution\Feedable\Famitsu\Enums\Category;

class FamitsuServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::prefix('famitsu')->group(function () {
            Route::get('category/{category}', FamitsuCategoryAction::class)
                ->whereIn('category', Category::cases());
        });
    }
}
