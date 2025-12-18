<?php

declare(strict_types=1);

namespace Revolution\Feedable\Core\Response;

use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\Response;

readonly class JsonFeedResponse implements Responsable
{
    public function __construct(
        protected ?string $title = null,
        protected ?string $home_page_url = null,
        protected ?string $feed_url = null,
        protected ?string $description = null,
        protected ?string $next_url = null,
        protected ?string $icon = null,
        protected ?string $favicon = null,
        protected ?array $authors = null,
        protected string $language = 'ja',
        protected ?array $hubs = null,
        protected array $items = [],
    ) {
        //
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toResponse($request): Response
    {
        $json = [
            'version' => 'https://jsonfeed.org/version/1.1',
            'title' => $this->title,
            'home_page_url' => $this->home_page_url,
            'feed_url' => $this->feed_url,
            'description' => $this->description,
            'next_url' => $this->next_url,
            'icon' => $this->icon,
            'favicon' => $this->favicon,
            'authors' => $this->authors,
            'language' => $this->language,
            'hubs' => $this->hubs,
            'items' => $this->items(),
        ];

        return response(json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))
            ->header('Content-Type', 'application/feed+json; charset=UTF-8');
    }

    protected function items(): array
    {
        // itemsは純粋な配列な場合もFeedItemの配列な場合もある
        return array_map(fn ($item) => is_array($item) ? $item : $item->toArray(), $this->items);
    }
}
