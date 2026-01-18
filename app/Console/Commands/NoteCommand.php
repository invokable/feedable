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
use Revolution\Feedable\Drivers\Note\NoteIndexDriver;
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
    public function handle(): void
    {
        // Vercelでagent-browserが使えるようになるまではGitHub Actionsでこのコマンドを実行して代用。
        // 一時的なものの予定なので簡易的に。

        $items = new NoteIndexDriver()->handle();

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
}
