<?php

declare(strict_types=1);

namespace Revolution\Feedable\Mirror;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MirrorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /**
         * /mirror?rss=https:// で入力されたRSSをそのまま返す最小のドライバー
         */
        Route::prefix('mirror')->group(function () {
            Route::get('/', function (Request $request) {
                $request->validate([
                    'rss' => 'required|url',
                ]);

                $response = Http::get($request->input('rss'));

                return response($response->body())
                    ->header('Content-Type', 'application/xml');
            });
        });
    }
}
