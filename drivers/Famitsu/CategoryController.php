<?php

declare(strict_types=1);

namespace Revolution\Feedable\Famitsu;

use const Dom\HTML_NO_DEFAULT_NS;

use Dom\HTMLDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController
{
    protected string $baseUrl = 'https://www.famitsu.com';

    protected ?string $buildId = null;

    public function __invoke(Request $request, string $category): array
    {
        $this->buildId = $this->getBuildId();
        if (empty($this->buildId)) {
            return [
                'error' => 'Unable to fetch buildId',
            ];
        }

        $response = Http::baseUrl($this->baseUrl)
            ->get("/_next/data/$this->buildId/category/$category/page/1.json");

        if ($response->failed()) {
            return [
                'error' => 'Unable to fetch category data',
            ];
        }

        if (app()->isLocal()) {
            Storage::put('famitsu/'.$category.'.json', $response->body());
        }

        $items = $response->collect('pageProps.categoryArticleDataForPc')
            ->reject(fn ($item) => Arr::has($item, 'advertiserName'))
            ->map(function ($item) {
                $publicationDate = Str::of(data_get($item, 'publishedAt'))->take(7)->remove('-')->toString();

                return [
                    'title' => data_get($item, 'title'),
                    'link' => $this->baseUrl.'/article/'.$publicationDate.'/'.data_get($item, 'id'),
                    'pubDate' => Carbon::parse(data_get($item, 'publishedAt'))->toRssString(),
                    'publicationDate' => $publicationDate,
                    'articleId' => data_get($item, 'id'),
                ];
            })
            ->map(function ($item) {

                $response = Http::baseUrl($this->baseUrl)
                    ->get("/_next/data/$this->buildId/article/".data_get($item, 'publicationDate').'/'.data_get($item, 'articleId').'.json');

                if ($response->failed()) {
                    return null;
                }

                if (app()->isLocal()) {
                    Storage::put('famitsu/'.data_get($item, 'articleId').'.json', $response->body());
                }

                $article = $response->collect('pageProps.articleDetailData');

                $author = collect($article->get('authors'))->map(fn ($author) => data_get($author, 'name_ja'))->join(', ');
                if (empty($author)) {
                    $author = data_get($article, 'user.name_ja');
                }
                $item['author'] = $author;

                $item['thumbnail'] = data_get($article, 'ogpImageUrl', data_get($article, 'thumbnailUrl'));

                $item['content'] = data_get($article, 'content');

                return $item;
            })
            ->toArray();

        return [
            'items' => $items,
            'buildId' => $this->buildId,
            'category' => $category,
        ];
    }

    protected function getBuildId(): ?string
    {
        $response = Http::get($this->baseUrl);

        $html = HTMLDocument::createFromString(
            source: $response->body(),
            options: LIBXML_HTML_NOIMPLIED | LIBXML_NOERROR | HTML_NO_DEFAULT_NS,
        );

        $json = $html->querySelector('#__NEXT_DATA__')->innerHTML;

        return data_get(json_decode($json, true), 'buildId');
    }

    protected function renderContentJson($content)
    {
    }
}
