<?php

declare(strict_types=1);

namespace Revolution\Feedable\JsonFeed;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Revolution\Feedable\Core\Contracts\FeedableDriver;
use Revolution\Feedable\Core\JsonFeed\JsonFeed;
use Revolution\Feedable\Core\Response\ErrorResponse;

class JsonFeedDriver implements FeedableDriver
{
    protected string $url;

    protected ?int $limit = null;

    public function __invoke(Request $request): Response|ErrorResponse
    {
        $this->url = $request->input('url');
        $this->limit = (int) $request->input('limit');

        try {
            $json = $this->handle();
        } catch (Exception $e) {
            return new ErrorResponse(
                error: 'Whoops! Something went wrong.',
                message: $e->getMessage(),
            );
        }

        return response($json)->header('Content-Type', 'application/json');
    }

    /**
     * Handle the feed conversion.
     * Convert RSS/Atom to JSON Feed.
     *
     * @throws Exception
     */
    public function handle(): string
    {
        $body = cache()->flexible('jsonfeed:'.md5($this->url.$this->limit), [now()->plus(minutes: 10), now()->plus(hours: 1)], function () {
            return Http::get($this->url)->throw()->body();
        });

        return app(JsonFeed::class)->convert($body, $this->url, $this->limit);
    }

    public function with(string $url, ?int $limit = null): static
    {
        // テスト時にセットする用

        $this->url = $url;
        $this->limit = $limit;

        return $this;
    }
}
