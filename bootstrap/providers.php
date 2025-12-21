<?php

declare(strict_types=1);

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\VoltServiceProvider::class,

    Revolution\Feedable\Famitsu\FamitsuServiceProvider::class,
    Revolution\Feedable\JumpPlus\JumpPlusServiceProvider::class,
    Revolution\Feedable\Mirror\MirrorServiceProvider::class,
    Revolution\Feedable\ComicDays\ComicDaysServiceProvider::class,
    Revolution\Feedable\Nintendo\NintendoServiceProvider::class,
    Revolution\Feedable\JsonFeed\JsonFeedServiceProvider::class,
    Revolution\Feedable\MagazinePocket\MagazinePocketServiceProvider::class,
    Revolution\Feedable\Laravel\LaravelServiceProvider::class,
];
