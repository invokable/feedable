<?php

declare(strict_types=1);

namespace Revolution\Feedable\Core\Response;

use Illuminate\Contracts\Support\Responsable;

class ResponseFactory
{
    public function __construct(protected string $format = 'rss') {}

    public static function format(string $format = 'rss'): static
    {
        return new static($format);
    }

    /**
     * TODO: 共通で使える引数を設定しつつ各フォーマットのレスポンスを生成
     */
    public function make(
        ?string $title = null,
        ?string $home_page_url = null,
        ?string $feed_url = null,
        ?string $description = null,
        ?string $next_url = null,
        ?string $icon = null,
        ?string $favicon = null,
        ?array $authors = null,
        string $language = 'ja',
        ?array $hubs = null,
        array $items = [],
    ): Responsable {
        return match ($this->format) {
            'json' => new JsonFeedResponse(
                title: $title,
                home_page_url: $home_page_url,
                feed_url: $feed_url,
                description: $description,
                next_url: $next_url,
                icon: $icon,
                favicon: $favicon,
                authors: $authors,
                language: $language,
                hubs: $hubs,
                items: $items,
            ),
            'atom' => new AtomResponse,
            default => new Rss2Response(
                title: $title,
                description: $description,
                link: $home_page_url,
                pubDate: now()->toRssString(),
                image: $icon,
                items: $items,
                language: $language,
            ),
        };
    }
}
