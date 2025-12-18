<?php

declare(strict_types=1);

namespace Revolution\Feedable\ComicDays;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Revolution\Feedable\Core\Driver;

class ComicDaysServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Driver::about(
            id: 'comic-days',
            name: 'コミックDAYS オリジナル',
            url: 'https://comic-days.com/',
            tags: ['manga'],
            description: 'コミックDAYSの今日更新された無料連載の最新話一覧。復刻作品も含まれます。',
            example: '/comic-days/original',
            language: 'ja',
        );
    }

    public function boot(): void
    {
        Route::prefix('comic-days')->group(function () {
            Route::get('original', ComicDaysDriver::class);
        });
    }
}
