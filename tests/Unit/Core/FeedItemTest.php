<?php

declare(strict_types=1);

use Revolution\Feedable\Core\Elements\FeedItem;

test('feed item', function () {
    $feed = new FeedItem(
        title: 'Sample Title',
        link: 'http://example.com/sample-link',
        description: 'This is a sample description for the feed item.',
    );
    $feed->set('author', 'Author')
        ->set('nonexistent', 'Some Value');

    $feed->when(true, function (FeedItem $item) {
        $item->test = 'Extra Property';
    });

    expect($feed->toArray())
        ->toBeArray()
        ->toMatchArray([
            'id' => null,
            'title' => 'Sample Title',
            'guid' => null,
            'link' => 'http://example.com/sample-link',
            'author' => 'Author',
            'pubDate' => null,
            'description' => 'This is a sample description for the feed item.',
            'thumbnail' => null,
            'categories' => null,
            'nonexistent' => 'Some Value',
            'test' => 'Extra Property',
        ])
        ->and($feed->get('id', 'default-id'))
        ->toBe('default-id')
        ->and($feed->get('test'))
        ->toBe('Extra Property')
        ->and($feed->test)
        ->toBe('Extra Property')
        ->and($feed->get('test2'))
        ->toBeNull();
});
