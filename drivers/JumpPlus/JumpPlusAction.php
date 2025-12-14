<?php

declare(strict_types=1);

namespace Revolution\Feedable\JumpPlus;

use DOMDocument;
use DOMXPath;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Revolution\Feedable\Core\Response\ErrorResponse;

class JumpPlusAction
{
    protected string $baseUrl = 'https://shonenjumpplus.com/';

    protected string $rssUrl = 'https://shonenjumpplus.com/rss';

    public function __invoke(): Responsable|Response
    {
        $links = $this->getDailySeries();

        // 公式RSSから$linksに含まれてるURLだけ返す
        $response = Http::get($this->rssUrl);
        if ($response->failed()) {
            return new ErrorResponse(
                error: 'Unable to fetch RSS',
            );
        }

        $xml = new DOMDocument();
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;

        $xml->loadXML($response->body());
        $xpath = new DOMXPath($xml);
        $items = $xpath->query('//item');

        foreach ($items as $item) {
            $linkNode = $item->getElementsByTagName('link')->item(0);
            if ($linkNode && ! in_array($linkNode->nodeValue, $links, true)) {
                $item->parentNode->removeChild($item);
            }
        }

        return response($xml->saveXML())
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    protected function getDailySeries(): ?array
    {
        $response = Http::get($this->baseUrl);

        if ($response->failed()) {
            return null;
        }

        if (app()->isLocal()) {
            Storage::put('jumpplus/daily.json', $response->body());
        }

        $dom = new DOMDocument();
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
