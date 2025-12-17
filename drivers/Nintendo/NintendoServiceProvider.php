<?php

declare(strict_types=1);

namespace Revolution\Feedable\Nintendo;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Revolution\Feedable\Core\Driver;

class NintendoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // idさえ違っていれば複数のドライバー情報を登録できる。
        // Driver::about(
        //     id: 'nintendo-direct',
        //     name: '任天堂 ニンテンドーダイレクト',
        //     url: 'https://www.nintendo.com/jp/nintendo-direct/',
        //     description: 'ニンテンドーダイレクト',
        //     example: '/nintendo/direct',
        //     lang: 'ja',
        // );

        Driver::about(
            id: 'nintendo-ir-news',
            name: '任天堂 IRニュース',
            url: 'https://www.nintendo.co.jp/ir/news/index.html',
            categories: ['game'],
            description: '任天堂のIRニュース',
            example: '/nintendo/ir/news',
            lang: 'ja',
        );
    }

    public function boot(): void
    {
        Route::prefix('nintendo')->group(function () {
            Route::get('ir/news', IRNewsController::class);
        });
    }
}
