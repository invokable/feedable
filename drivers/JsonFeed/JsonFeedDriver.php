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

    public function __invoke(Request $request): Response|ErrorResponse
    {
        $this->url = $request->input('url');

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
        $body = Http::get($this->url)->throw()->body();

        return app(JsonFeed::class)->convert($body, $this->url);
    }
}
