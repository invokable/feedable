<?php

declare(strict_types=1);

namespace Revolution\Feedable\JumpPlus;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Revolution\Feedable\Core\Driver;

class JumpPlusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Driver::about(
            id: 'shonenjumpplus',
            name: '少年ジャンプ＋',
            url: 'https://shonenjumpplus.com/',
            categories: ['manga'],
            description: '少年ジャンプ＋の最新マンガ記事を取得します。公式RSSから旧作を除いた新作のみのRSSです。',
            example: '/shonenjumpplus/daily',
            lang: 'ja',
        );
    }

    public function boot(): void
    {
        Route::prefix('shonenjumpplus')->group(function () {
            Route::get('daily', JumpPlusAction::class);
        });
    }
}
