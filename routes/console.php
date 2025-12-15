<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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

Artisan::command('rsshub-jp', function () {
    // RSSHubから日本語ルートを取得してmarkdownファイルを生成

    $list = collect(File::glob('RSSHub/lib/routes/*/namespace.ts'))
        ->filter(fn ($file) => str_contains(File::get($file), "lang: 'ja'"))
        ->map(fn ($file) => Str::between($file, 'routes/', '/namespace.ts'))
        ->map(fn ($name) => "- [ ] {$name}")
        ->implode(PHP_EOL);

    File::put('docs/routes-jp.md', "# RSSHub 日本語ルート一覧\n\n{$list}\n");
});

Artisan::command('rsshub-en', function () {
    $list = collect(File::glob('RSSHub/lib/routes/*/namespace.ts'))
        ->filter(fn ($file) => str_contains(File::get($file), "lang: 'en'"))
        ->map(fn ($file) => Str::between($file, 'routes/', '/namespace.ts'))
        ->map(fn ($name) => "- [ ] {$name}")
        ->implode(PHP_EOL);

    File::put('docs/routes-en.md', "# RSSHub 英語ルート一覧\n\n{$list}\n");
});
