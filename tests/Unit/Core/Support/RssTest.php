<?php

declare(strict_types=1);

use Revolution\Feedable\Core\Support\RSS;

$xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
<channel>
    <title>Example RSS Feed</title>
    <link>https://example.com/</link>
    <description>This is an example RSS feed</description>
    <item>
        <title>Item 1</title>
        <link>https://example.com/item1</link>
        <description>Description for Item 1</description>
    </item>
    <item>
        <title>Item 2</title>
        <link>https://example.com/item2</link>
        <description>Description for Item 2</description>
    </item>
</channel>
</rss>
XML;

test('RSS eachItems', function () use ($xml) {
    $result = RSS::eachItems($xml, function (DOMElement $item) {
        $titleNode = $item->getElementsByTagName('title')->item(0);
        if ($titleNode) {
            $titleNode->nodeValue = 'Modified '.$titleNode->nodeValue;
        }
    });

    expect($result)->toContain('Modified Item 1')
        ->and($result)->toContain('Modified Item 2');
});

test('RSS filterLinks', function () use ($xml) {
    $links = ['https://example.com/item1'];
    $result = RSS::filterLinks($xml, $links);

    expect($result)->toContain('Item 1')
        ->and($result)->not->toContain('Item 2');
});

test('RSS rejectLinks', function () use ($xml) {
    $links = ['https://example.com/item1'];
    $result = RSS::rejectLinks($xml, $links);

    expect($result)->toContain('Item 2')
        ->and($result)->not->toContain('Item 1');
});
