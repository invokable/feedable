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

### @sparticuz/chromium

@sparticuz/chromiumは`chromium.executablePath()`実行時にbrファイルを`/tmp/chromium`に展開している。
`scripts/install-chromium.js`にインストールするだけのスクリプトを用意してデプロイ時に実行。
`/tmp/chromium`は生成されているのでここは問題ないはず。

### GitHub Actions環境では成功

Ubuntuなので普通にインストールするだけで使える。

```shell
npm install -g agent-browser
agent-browser install --with-deps
npm install @sparticuz/chromium
node ./scripts/install-chromium.js
```

Vercelで`npm install -g agent-browser`を使った場合は`/node24/bin/agent-browser`にインストールされる。
