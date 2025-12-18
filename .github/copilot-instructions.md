# Feedable Project Guidelines

Feedableは [RSSHub](https://github.com/DIYgod/RSSHub) を参考にしたRSSフィード生成サービス。RSSHubは日本向けサイトの登録が少ないので別途開発。

## 技術スタック
- PHP 8.3+
- Laravel 12
- Vercel でデータベースなしでも動くようにする。PHP8.3しか使えないので8.3に合わせる。
- Playwrightを使ったデータ取得もできるけどVercelでは動かすのが難しい。Laravel Forge向け。

## ドライバー
各サイトのフィード生成コードはドライバーとして分離。最終的には普通のLaravel用composerパッケージとしてインストールできるようにする予定。
`./drivers` 配下に各サイトのフィード生成コードを配置。
入口のルーティングから出口のレスポンスまで全てドライバーで制御可能。
サイト毎に細かい調整が必要になることは分かっているので厳密なパターンは適用せず最大限の柔軟性を持たせる。

現在はRSSHubにある日本語のサイトを移植中。

## スクレイピング
- LaravelのHTTPクライアント: これで取得できるなら一番簡単。
- Playwright(`revolution/salvager`): JavaScriptで動的に生成されるページを取得する場合に使う。Vercelでは動かせない。
- Cloudflare Browser Rendering: Vercelでも使えるはず。個別にAPIトークンの設定が必要。無料プランでは1日10分まで。

## HTML解析
- DOMDocument: PHP8.3以下用。
- Dom\HTMLDocument: PHP8.4以上用。
- Symfony DomCrawler: 7.xはPHP8.1以上。8.0はPHP8.4以上。
- PlaywrightのLocator: `playwright-php/playwright`の`$page->locator()`はquerySelectorAllに似た使い方ができる。

## Feedable Core
`./core`はドライバーから使うヘルパー。

### Response
`AtomResponse`や`Rss2Response`で最終的な出力フォーマットを固定化する。フィードのフォーマットは統一されてないのでこれを使わなくてもいい。

PHP 8.4の時期なら名前付き引数を使うのがいいので以下のような使い方。
```php
use Revolution\Feedable\Core\Response\Rss2Response;

return new Rss2Response(
    title: $title,
    items: $items,
);
```

RSSかAtomかに拘る必要もなくなってるので基本的には`Rss2Response`を使う。

`ErrorResponse`はエラー時のレスポンス。htmlを返す。RSSHubでは詳細なエラー画面を表示しているので後で拡張。

### FeedItem
RSS2やAtomで共通のフィードアイテムオブジェクト。ドライバーで生成したデータをこのクラスに詰めてレスポンスに渡す。
使わなくてもいいのでbladeでは`data_get()`を使ってarrayでもオブジェクトでもいいようにしている。

### FeedableDriver

ドライバー用の契約=Interface。必須メソッドは`handle()`のみ。テストを書きやすいようにメインの処理を`handle()`、Routeからの入力・出力を`__invoke()`で分ける意図があるけどこれも使わなくてもいい。

### Support
Supportはstaticメソッドのみで構成されたヘルパー。

#### AbsoluteUri
`AbsoluteUri::resolve()`は相対URLを絶対URLに変換する。

```php
use Revolution\Feedable\Core\Support\AbsoluteUri;

$absoluteUrl = AbsoluteUri::resolve('https://example.com/', '/images/sample.jpg');
```

URLの組み立てにはなるべくLaravelの`Illuminate\Support\Uri`を使う。`/`の有無によるミスを防ぐため。
```php
use Illuminate\Support\Uri;
$url = Uri::of('https://example.com')->withPath('images/sample.jpg');
```

#### RSS
RSS操作ヘルパー。RSSは提供されているけど余計なitemが多い場合にフィルタリングしたり、タイトルや説明を修正したりするのに使う。

```php
use Revolution\Feedable\Core\Support\RSS;

// itemが多い場合に別のページから解析した$linksのみに絞る
$xml = RSS::filterLinks($rss, $links);
```

```php
use Revolution\Feedable\Core\Support\RSS;
use DOMElement;

// NGワードで除外したり
$xml = RSS::each($rss, function (DOMElement $item) {
    $title = $item->getElementsByTagName('title')->item(0);
    if ($title && str_contains($title->textContent, 'NGワード')) {
        $item->parentNode->removeChild($item);
    }
});
```

ほとんど同じ`Atom`クラスもある。

## デプロイ
VervelへのデプロイはDBなしなら簡単だけどDBを使ってキャッシュが推奨。
AWS RDSでDBを用意するかSupabaseなどの無料DBを使う。

### SupabaseのDBを使う場合
Vercelの環境設定で

- `DB_URL`: SupabaseのPostgres接続URL。VercelではDirect connectionは使えないのでTransaction poolerのURLを指定する。
Supabaseの**Connect**画面で以下のようなURLが表示される箇所を探す。
```
DB_URL=postgresql://postgres.*****:[YOUR-PASSWORD]@*****.pooler.supabase.com:6543/postgres
```

- `DB_CONNECTION`: `pgsql`

## カスタムドライバー

普通のLaravelのルーティングなのでフォークしたプロジェクトでカスタムドライバーを作るには `routes/web.php` にルートを追加するだけ。

composerパッケージとして作る場合はServiceProviderでルートを登録。

`Driver::about()`でドライバー情報を登録。対応サイトリストに表示するための情報なので登録しなくても使える。
