<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Revolution\Feedable\JumpPlus\JumpPlusAction;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('jump', function () {
    $this->comment('Fetching Shonen Jump Plus feed...');
    $jump = new JumpPlusAction;
    dump($jump()->getContent());
    $this->comment('Done.');
})->purpose('Fetch Shonen Jump Plus feed');
