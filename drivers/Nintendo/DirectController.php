<?php

declare(strict_types=1);

namespace Revolution\Feedable\Nintendo;

use App\Http\Controllers\Controller;
use DOMDocument;
use DOMXPath;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;
use Revolution\Feedable\Core\Elements\FeedItem;
use Revolution\Feedable\Core\Response\ErrorResponse;
use Revolution\Feedable\Core\Response\Rss2Response;

class DirectController extends Controller
{
    protected string $baseUrl = 'https://www.nintendo.com/jp/nintendo-direct/';

    public function __invoke(): Responsable
    {
        /**
         * baseUrlのリダイレクト先が最新のニンテンドーダイレクトページになるのでtitleとlinkを取得して返す。
         */
        $response = Http::get($this->baseUrl);

        $redirect = new DOMDocument;
        @$redirect->loadHTML($response->body());
        $xpath = new DOMXPath($redirect);
        $refresh = $xpath->query('//meta[@http-equiv="Refresh"]');

        $content = $refresh->item(0)?->getAttribute('content') ?? '';
        preg_match('/URL=(.+)$/', $content, $matches);
        if (! isset($matches[1])) {
            return new ErrorResponse(
                error: 'Unable to fetch link',
            );
        }
        $link = $matches[1];

        // linkから日付
        // https://www.nintendo.com/jp/nintendo-direct/20250912/index.html
        $date = Str::of(Uri::of($link)->path())->dirname()->afterLast('/')->toString();

        if (Carbon::canBeCreatedFromFormat($date, 'Ymd')) {
            $pubDate = Carbon::createFromFormat('Ymd', $date)
                ->setTime(0, 0, 0)
                ->toRssString();
        } else {
            // linkから日付が取得できなかった場合は現在日時にする
            $pubDate = now()->toRssString();
        }

        $response = Http::get($link);

        $direct = new DOMDocument;
        @$direct->loadHTML($response->body());
        $xpath = new DOMXPath($direct);

        $descriptionNode = $xpath->query('//meta[@name="description"]');
        $description = $descriptionNode->item(0)?->getAttribute('content');

        $title = $direct->getElementsByTagName('title')->item(0)?->textContent;

        $items = [
            new FeedItem(
                title: $title,
                link: $link,
                pubDate: $pubDate,
                description: $description,
            ),
        ];

        return new Rss2Response(
            title: 'ニンテンドーダイレクト',
            description: '最新のニンテンドーダイレクト',
            link: $this->baseUrl,
            items: $items,
        );
    }
}
