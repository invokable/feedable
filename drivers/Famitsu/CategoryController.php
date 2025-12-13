<?php

declare(strict_types=1);

namespace Revolution\Feedable\Famitsu;

use const Dom\HTML_NO_DEFAULT_NS;

use Dom\HTMLDocument;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Revolution\Feedable\Core\Response\ErrorResponse;
use Revolution\Feedable\Core\Response\Rss2Response;

class CategoryController
{
    protected string $baseUrl = 'https://www.famitsu.com';

    protected ?string $buildId = null;

    public function __invoke(Request $request, string $category): Responsable
    {
        $this->getBuildId();

        if (empty($this->buildId)) {
            return new ErrorResponse(
                error: 'Unable to fetch buildId',
            );
        }

        $response = Http::baseUrl($this->baseUrl)
            ->get("/_next/data/$this->buildId/category/$category/page/1.json");

        if ($response->failed()) {
            // タイミングによってはここでjsonが取得できないことがあるので後で詳細なエラーメッセージに更新。
            return new ErrorResponse(
                error: 'Unable to fetch category data',
            );
        }

        if (app()->isLocal()) {
            Storage::put('famitsu/'.$category.'.json', $response->body());
        }

        $title = $response->json('pageProps.targetCategory.nameJa', '').'の最新記事 | ゲーム・エンタメ最新情報のファミ通.com';

        $items = $response->collect('pageProps.categoryArticleDataForPc')
            ->reject(fn ($item) => Arr::has($item, 'advertiserName'))
            ->map(function ($item) {
                // カテゴリーのjsonから記事リストに変換
                $publicationDate = Str::of(data_get($item, 'publishedAt'))->take(7)->remove('-')->toString();

                $categories = collect(data_get($item, 'subCategories', []))
                    ->map(fn ($sub) => data_get($sub, 'nameJa'))
                    ->prepend(data_get($item, 'mainCategory.nameJa'))
                    ->toArray();

                return [
                    'title' => data_get($item, 'title'),
                    'link' => $this->baseUrl.'/article/'.$publicationDate.'/'.data_get($item, 'id'),
                    'pubDate' => Carbon::parse(data_get($item, 'publishedAt'))->toRssString(),
                    'publicationDate' => $publicationDate,
                    'categories' => $categories,
                    'articleId' => data_get($item, 'id'),
                ];
            })
            ->map(function ($item) {
                // 記事詳細のjsonを取得。一度取得すればいいので長くキャッシュ
                return Cache::remember('famitsu_article_'.$this->buildId.'_'.data_get($item, 'articleId'),
                    now()->addDays(7),
                    function () use ($item) {
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

                        $item['description'] = $this->renderJson(data_get($article, 'content'));

                        return $item;
                    });
            })
            ->values()
            ->toArray();

        return new Rss2Response(
            title: $title,
            description: $title,
            link: $this->baseUrl.'/category/'.$category.'/page/1',
            image: 'https://www.famitsu.com/res/images/headIcons/apple-touch-icon.png',
            items: $items,
        );
    }

    protected function getBuildId(): void
    {
        $response = Http::get($this->baseUrl);

        $html = HTMLDocument::createFromString(
            source: $response->body(),
            options: LIBXML_HTML_NOIMPLIED | LIBXML_NOERROR | HTML_NO_DEFAULT_NS,
        );

        $json = $html->querySelector('#__NEXT_DATA__')->innerHTML;

        $this->buildId = data_get(json_decode($json, true), 'buildId');
    }

    protected function renderJson(array $content): string
    {
        return collect($content)
            ->map(fn ($c) => collect(data_get($c, 'contents', []))
                ->map(fn ($con) => $this->renderContent($con))
                ->join(''),
            )
            ->join('');
    }

    protected function renderContent(array $c): string
    {
        $content = data_get($c, 'content');

        if (is_array($content)) {
            $content = collect($content)
                ->map(fn ($con) => is_array($con) && isset($con['type']) ? $this->renderContent($con) : '')
                ->join('');
        }

        return match (data_get($c, 'type')) {
            'B', 'INTERVIEWEE', 'STRONG' => "<b>{$content}</b>",
            'HEAD' => "<h2>{$content}</h2>",
            'SHEAD' => "<h3>{$content}</h3>",
            'LINK_B', 'LINK_B_TAB' => '<a href="'.data_get($c, 'url').'"><b>'.$content.'</b></a><br>',
            'IMAGE' => '<img src="'.data_get($c, 'path').'">',
            'NEWS' => '<a href="'.data_get($c, 'url').'">'.$content.'<br>'.data_get($c, 'description').'</a><br>',
            'HTML' => $content,
            'ANNOTATION', 'CAPTION', 'ITEMIZATION', 'ITEMIZATION_NUM', 'NOLINK', 'STRING', 'TWITTER', 'YOUTUBE' => "<div><span>{$content}</span></div>",
            'BUTTON', 'BUTTON_ANDROID', 'BUTTON_EC', 'BUTTON_IOS', 'BUTTON_TAB', 'LINK', 'LINK_TAB' => '<a href="'.data_get($c, 'url').'">'.$content.'</a><br>',
            default => '',
        };
    }
}
