<?php

declare(strict_types=1);

namespace Revolution\Feedable\Core\Response;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

readonly class Rss2Response implements Responsable
{
    public function __construct(
        protected ?string $title = null,
        protected ?string $description = null,
        protected ?string $link = null,
        protected ?string $pubDate = null,
        protected ?string $image = null,
        protected ?array $items = null,
        protected string $language = 'ja',
        protected int $ttl = 5,
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
        $rss = Blade::render(File::get(__DIR__.'/views/rss2.blade.php'), [
            'title' => $this->title,
            'description' => $this->description,
            'link' => $this->link,
            'pubDate' => $this->pubDate,
            'image' => $this->image,
            'language' => $this->language,
            'ttl' => $this->ttl,
            'items' => $this->items,
        ]);

        return response($rss)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
