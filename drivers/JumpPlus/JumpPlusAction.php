<?php

declare(strict_types=1);

namespace Revolution\Feedable\JumpPlus;

use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Revolution\Feedable\Core\Contracts\FeedableDriver;
use Revolution\Feedable\Core\Response\ErrorResponse;
use Revolution\Feedable\Core\Support\RSS;

class JumpPlusAction implements FeedableDriver
{
    protected string $baseUrl = 'https://shonenjumpplus.com/';

    protected string $rssUrl = 'https://shonenjumpplus.com/rss';

    public function __invoke(): Responsable|Response
    {
        try {
            $xml = $this->handle();
        } catch (Exception $e) {
            return new ErrorResponse(
                error: 'Whoops! Something went wrong.',
                message: $e->getMessage(),
            );
        }

        return response($xml)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * @throws Exception
     */
    public function handle(): string
    {
        $links = $this->getDailySeries();

        // 公式RSSから$linksに含まれてるURLだけ返す
        $response = Http::get($this->rssUrl)->throw();

        return RSS::filterLinks($response->body(), $links);
    }

    protected function getDailySeries(): ?array
    {
        $response = Http::get($this->baseUrl);

        if ($response->failed()) {
            return null;
        }

        if (app()->isLocal()) {
            Storage::put('jumpplus/daily.html', $response->body());
        }

        $dom = new DOMDocument;
        @$dom->loadHTML($response->body());
        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//li[contains(@class, "daily-series-item")]/a');
        $links = [];
        foreach ($nodes as $node) {
            $links[] = $node->getAttribute('href');
        }

        return $links;
    }
}
