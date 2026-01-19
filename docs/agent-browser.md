# agent-browser をVercelサーバーレス環境で使う

agent-browserと軽量の`@sparticuz/chromium`でブラウザの起動まではできたけどhtmlの取得はできないところまで。
https://github.com/vercel-labs/agent-browser
https://github.com/Sparticuz/chromium

vercel-phpはcomposer.jsonのscripts->vercelでデプロイ時のコマンドを実行できる。以降はここでコマンドを実行した試行錯誤の記録。

### agent-browser installを実行した場合
`@sparticuz/chromium`を使っているのでplaywrightのinstallは不要なはずだけど
一応`agent-browser install --with-deps`を実行したらsudoがなく失敗。

```
node_modules/agent-browser/bin/agent-browser install --with-deps
Installing system dependencies...
Running: sudo dnf install -y nss nspr atk at-spi2-atk cups-libs libdrm libXcomposite libXdamage libXrandr mesa-libgbm pango alsa-lib libxkbcommon libxcb libX11-xcb libX11 libXext libXcursor libXfixes libXi gtk3 cairo-gobject
sh: line 1: sudo: command not found
```

`agent-browser install`を使わず`dnf`コマンドを直接指定すればインストールは成功した。それでもhtmlの取得はできないまま。
agent-browserを調査して他にもインストールが必要か確認。
https://github.com/vercel-labs/agent-browser/blob/main/cli/src/install.rs

デプロイ時はルート環境なのでsudoは関係ないけどここでインストールしてるのはビルド環境のみ。デプロイ後の実行環境とは別。引き継がれない。

### @sparticuz/chromium

@sparticuz/chromiumは`chromium.executablePath()`実行時にbrファイルを`/tmp/chromium`に展開している。
`scripts/install-chromium.js`にインストールするだけのスクリプトを用意してデプロイ時に実行。
`/tmp/chromium`は生成されているけどデプロイ後の環境にはない。
`/tmp/chromium`をコピーするとサイズ制限でデプロイ失敗。
デプロイ後に`/tmp/chromium`を生成しても次のリクエスト時にはない。

つまり
- デプロイ時はサイズの小さいbrファイルをnode_modules内に残す。
- 使用時に毎回`/tmp/chromium`を生成する。

これで進んだけど@sparticuz/chromiumだけではshared libraries不足でエラーだった。
> /tmp/chromium: error while loading shared libraries: libnspr4.so: cannot open shared object file: No such file or directory

`/tmp/chromium`と同様に`dnf install ...`で毎回インストールしてもshared librariesのエラー。デプロイ後の環境はルートではないので`error: Failed to create: /var/cache/yum/metadata`のエラー。

### Laravel Cloudでも動かない

Build commandsでインストールすれば良さそうだけどsudoコマンドがないけどルートでもないようで`apt-get install`で権限エラーになる。
`npm install -g agent-browser`のインストール自体は可能。`apt-get install`さえできれば動きそう。

試してないけどLambdaのLaravel Vaporでも動かない、Laravel Forgeなら動くはず。
VaporはDocker Runtimesなら動くかも。

### GitHub Actions環境では成功

Ubuntuなので普通にインストールするだけで使える。

```shell
npm install -g agent-browser
agent-browser install --with-deps
npm install @sparticuz/chromium
node ./scripts/install-chromium.js
```

Vercelで`npm install -g agent-browser`を使った場合は`/node24/bin/agent-browser`にインストールされる。デプロイ後には消えてるのでglobalではなくプロジェクトでインストール。

## 現状

shared librariesのエラーで停滞。agent-browserは登場したばかりなので引き続き情報を集める。
