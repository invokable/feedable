<?php

declare(strict_types=1);

namespace Revolution\Feedable\JsonFeed;

use DOMDocument;
use DOMElement;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Revolution\Feedable\Core\Contracts\FeedableDriver;
use Revolution\Feedable\Core\Response\ErrorResponse;

class JsonFeedDriver implements FeedableDriver
{
    protected string $url;

    protected const string XML_CONTENT_NS = 'http://purl.org/rss/1.0/modules/content/';

    protected const string XML_DC_NS = 'http://purl.org/dc/elements/1.1/';

    protected const string XML_MEDIA_NS = 'http://search.yahoo.com/mrss/';

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

        return match ($this->detect($body)) {
            'rdf' => $this->rdf($body),
            'rss2' => $this->rss2($body),
            'atom' => $this->atom($body),
            'json' => $body,
            default => throw new Exception('Unsupported feed format.'),
        };
    }

    /**
     * Detect feed format.
     * RDF(RSS0.x), RSS2, Atom, JSON Feed.
     */
    protected function detect(string $body): string
    {
        if (str_contains($body, '<rdf:RDF')) {
            return 'rdf';
        }

        if (str_contains($body, '<rss')) {
            return 'rss2';
        }

        if (str_contains($body, '<feed')) {
            return 'atom';
        }

        if (str_contains(data_get(json_decode($body, true), 'version') ?? '', 'https://jsonfeed.org/version/1')) {
            return 'json';
        }

        return 'unknown';
    }

    protected function rdf(string $body): string
    {
        $doc = new DOMDocument;
        $doc->loadXML($body);

        $channel = $doc->getElementsByTagName('channel')->item(0);

        $feed = [
            'version' => 'https://jsonfeed.org/version/1.1',
            'title' => $this->getNodeValue($channel, 'title'),
            'home_page_url' => $this->getNodeValue($channel, 'link'),
            'feed_url' => $this->url,
            'description' => $this->getNodeValue($channel, 'description'),
            'items' => [],
        ];

        $items = $doc->getElementsByTagName('item');

        foreach ($items as $item) {
            /** @var DOMElement $item */
            $contentHtml = $this->getNodeValueNS($item, 'encoded', self::XML_CONTENT_NS)
                ?? $this->getNodeValue($item, 'description');

            $feedItem = [
                'id' => $item->getAttribute('rdf:about') ?: $this->getNodeValue($item, 'link'),
                'url' => $this->getNodeValue($item, 'link'),
                'title' => $this->getNodeValue($item, 'title'),
                'content_html' => $contentHtml,
                'date_published' => $this->formatDate($this->getNodeValueNS($item, 'date', self::XML_DC_NS)),
            ];

            $author = $this->getNodeValueNS($item, 'creator', self::XML_DC_NS);
            if ($author) {
                $feedItem['authors'] = [['name' => $author]];
            }

            $subject = $this->getNodeValueNS($item, 'subject', self::XML_DC_NS);
            if ($subject) {
                $feedItem['tags'] = [$subject];
            }

            $feed['items'][] = array_filter($feedItem);
        }

        return json_encode($feed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    protected function rss2(string $body): string
    {
        $doc = new DOMDocument;
        $doc->loadXML($body);

        $channel = $doc->getElementsByTagName('channel')->item(0);

        $feed = [
            'version' => 'https://jsonfeed.org/version/1.1',
            'title' => $this->getNodeValue($channel, 'title'),
            'home_page_url' => $this->getNodeValue($channel, 'link'),
            'feed_url' => $this->url,
            'description' => $this->getNodeValue($channel, 'description'),
            'items' => [],
        ];

        $items = $doc->getElementsByTagName('item');

        foreach ($items as $item) {
            $feedItem = [
                'id' => $this->getNodeValue($item, 'guid') ?: $this->getNodeValue($item, 'link'),
                'url' => $this->getNodeValue($item, 'link'),
                'title' => $this->getNodeValue($item, 'title'),
                'content_html' => $this->getNodeValue($item, 'description'),
                'date_published' => $this->formatDate($this->getNodeValue($item, 'pubDate')),
                'image' => $this->getRss2Image($item),
            ];

            $author = $this->getNodeValue($item, 'author') ?: $this->getNodeValueNS($item, 'creator', self::XML_DC_NS);
            if ($author) {
                $feedItem['authors'] = [['name' => $author]];
            }

            $feed['items'][] = array_filter($feedItem);
        }

        return json_encode($feed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    protected function atom(string $body): string
    {
        $doc = new DOMDocument;
        $doc->loadXML($body);

        $feedElement = $doc->getElementsByTagName('feed')->item(0);

        $feed = [
            'version' => 'https://jsonfeed.org/version/1.1',
            'title' => $this->getNodeValue($feedElement, 'title'),
            'home_page_url' => $this->getAtomLink($feedElement, 'alternate'),
            'feed_url' => $this->getAtomLink($feedElement, 'self'),
            'description' => $this->getNodeValue($feedElement, 'subtitle'),
            'items' => [],
        ];

        $entries = $doc->getElementsByTagName('entry');

        foreach ($entries as $entry) {
            $feedItem = [
                'id' => $this->getNodeValue($entry, 'id'),
                'url' => $this->getAtomLink($entry, 'alternate'),
                'title' => $this->getNodeValue($entry, 'title'),
                'content_html' => $this->getNodeValue($entry, 'content') ?: $this->getNodeValue($entry, 'summary'),
                'summary' => $this->getNodeValue($entry, 'summary'),
                'date_published' => $this->formatDate($this->getNodeValue($entry, 'published')),
                'date_modified' => $this->formatDate($this->getNodeValue($entry, 'updated')),
                'image' => $this->getAtomImage($entry),
            ];

            $authorNode = $entry->getElementsByTagName('author')->item(0);
            if ($authorNode) {
                $feedItem['authors'] = [['name' => $this->getNodeValue($authorNode, 'name')]];
            }

            $feed['items'][] = array_filter($feedItem);
        }

        return json_encode($feed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    protected function getNodeValue(DOMElement $parent, string $tagName): ?string
    {
        $nodes = $parent->getElementsByTagName($tagName);
        if ($nodes->length === 0) {
            return null;
        }

        return trim($nodes->item(0)->textContent) ?: null;
    }

    protected function getNodeValueNS(DOMElement $parent, string $tagName, ?string $namespace = null): ?string
    {
        $nodes = $parent->getElementsByTagNameNS($namespace, $tagName);
        if ($nodes->length === 0) {
            return null;
        }

        return trim($nodes->item(0)->textContent) ?: null;
    }

    protected function getAtomLink(DOMElement $parent, string $rel): ?string
    {
        $links = $parent->getElementsByTagName('link');
        foreach ($links as $link) {
            if ($link->getAttribute('rel') === $rel || ($rel === 'alternate' && ! $link->hasAttribute('rel'))) {
                return $link->getAttribute('href') ?: null;
            }
        }

        return null;
    }

    /**
     * Get image from RSS2 item.
     * <enclosure url="https://" length="0" type="image/jpeg"/>
     * <media:content url="https://" type="image/jpeg" medium="image">
     * <media:thumbnail>https://</media:thumbnail>
     */
    protected function getRss2Image(DOMElement $item): ?string
    {
        // enclosure
        $enclosure = $item->getElementsByTagName('enclosure')->item(0);
        if ($enclosure && str_starts_with($enclosure->getAttribute('type'), 'image/')) {
            return $enclosure->getAttribute('url') ?: null;
        }

        // media:content
        $mediaContent = $item->getElementsByTagNameNS(self::XML_MEDIA_NS, 'content')->item(0);
        if ($mediaContent && $mediaContent->getAttribute('medium') === 'image') {
            return $mediaContent->getAttribute('url') ?: null;
        }

        // media:thumbnail
        $mediaThumbnail = $item->getElementsByTagNameNS(self::XML_MEDIA_NS, 'thumbnail')->item(0);
        if ($mediaThumbnail) {
            return $mediaThumbnail->getAttribute('url') ?: trim($mediaThumbnail->textContent) ?: null;
        }

        return null;
    }

    /**
     * Get image from Atom entry.
     * <link rel="enclosure" href="https://" length="0" type="image/jpeg" />
     */
    protected function getAtomImage(DOMElement $entry): ?string
    {
        $links = $entry->getElementsByTagName('link');
        foreach ($links as $link) {
            if ($link->getAttribute('rel') === 'enclosure' && str_starts_with($link->getAttribute('type'), 'image/')) {
                return $link->getAttribute('href') ?: null;
            }
        }

        return null;
    }

    protected function formatDate(?string $date = null): ?string
    {
        if (! $date) {
            return null;
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return null;
        }

        return date('c', $timestamp);
    }
}
