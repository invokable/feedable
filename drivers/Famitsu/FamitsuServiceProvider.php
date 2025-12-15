<?php

declare(strict_types=1);

namespace Revolution\Feedable\Famitsu;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Revolution\Feedable\Core\Driver;
use Revolution\Feedable\Famitsu\Enums\Category;

class FamitsuServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Driver::about(
            id: 'famitsu',
            name: 'ファミ通.com',
            url: 'https://famitsu.com/',
            categories: ['game'],
            description: $this->description(),
            example: url('/famitsu/category/new-article'),
            lang: 'ja',
        );
    }

    protected function description(): string
    {
        $cat = collect(Category::cases())
            ->map(fn (Category $category) => "- `{$category->value}`")
            ->implode(PHP_EOL);

        return <<<MARKDOWN
以下のカテゴリーから記事を取得できます。
{$cat}
MARKDOWN;
    }

    public function boot(): void
    {
        Route::prefix('famitsu')->group(function () {
            Route::get('category/{category}', FamitsuCategoryAction::class)
                ->whereIn('category', Category::cases());
        });
    }
}
