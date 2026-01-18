<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Revolution\Feedable\Core\Elements\Author;
use Revolution\Feedable\Core\Elements\FeedItem;
use Revolution\Feedable\Core\Enums\Format;
use Revolution\Feedable\Core\Enums\Timezone;
use Revolution\Feedable\Core\Response\ResponseFactory;
use Revolution\Feedable\Core\Support\AbsoluteUri;
use Revolution\Salvager\AgentBrowser;
use Revolution\Salvager\Facades\Salvager;
use Symfony\Component\DomCrawler\Crawler;

class NoteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feedable:note';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected string $baseUrl = 'https://note.com/';

    protected string $postUrl = 'https://feedable-rss.vercel.app/note/post';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $items = $this->feed();

        $rss = ResponseFactory::format(Format::RSS)->make(
            title: 'note 注目記事',
            home_page_url: $this->baseUrl,
            feed_url: 'https://feedable-rss.vercel.app/note/index.rss',
            description: 'note 注目記事',
            items: $items,
        );

        Http::post($this->postUrl, [
            'feed' => $rss->toResponse(request())->getContent(),
            'format' => Format::RSS->value,
            'token' => config('feedable.note.token'),
        ]);

        $json = ResponseFactory::format(Format::JSON)->make(
            title: 'note 注目記事',
            home_page_url: $this->baseUrl,
            feed_url: 'https://feedable-rss.vercel.app/note/index.json',
            description: 'note 注目記事',
            items: $items,
        );

        Http::post($this->postUrl, [
            'feed' => $json->toResponse(request())->getContent(),
            'format' => Format::JSON->value,
            'token' => config('feedable.note.token'),
        ]);
    }

    public function feed(): array
    {
        // agent-browserで取得するサンプル。
        Salvager::agent(function (AgentBrowser $agent) use (&$html) {
            // ブラウザで開く
            $agent->open($this->baseUrl);
            // ページの読み込み完了を待つ
            $agent->run('wait --load networkidle');

            // HTMLを取得
            // css=はCSSセレクタで要素を指定できる
            // xpath=はXPathで要素を指定
            $html = $agent->html('css=body');

            // ここで複雑なことはせずhtmlだけ取得してすぐに抜ける

            // ブラウザを閉じる。省略化。
            $agent->close();
        });

        if (app()->runningUnitTests()) {
            Storage::put('note/index.html', $html);
        }

        $crawler = new Crawler($html);

        $items = $crawler->filter('section.m-horizontalScrollingList')
            ->first()
            ->filter('div.m-largeNoteWrapper__card')
            ->each(function (Crawler $node) {
                $title = $node->filter('h3')->text();
                $link = $node->filter('a')->attr('href');
                if (empty($link)) {
                    return null;
                }
                $link = AbsoluteUri::resolve($this->baseUrl, $link);

                $image = $node->filter('img.m-thumbnail__image')->attr('src');
                if (Str::startsWith($image, 'data:')) {
                    $image = null;
                }

                $author = $node->filter('span.o-verticalTimeLineNote__userText')->text();

                $date = $node->filter('time')->text();
                // ○時間前、○日前、○年前などをCarbon::parse可能な英語に
                $date = str_replace(
                    ['前', '時間', '分', '日', '週間', 'か月', '年'],
                    [' ago', ' hours', ' minutes', ' days', ' weeks', ' months', ' years'],
                    $date,
                );

                return new FeedItem(
                    id: $link,
                    url: $link,
                    title: $title,
                    image: $image,
                    date_published: Carbon::parse($date, Timezone::AsiaTokyo->value),
                    authors: [Author::make(name: $author)->toArray()],
                );
            });

        return collect($items)->filter()->toArray();
    }
}
