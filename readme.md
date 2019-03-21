# LukiWiki

LukiWikiとは[Laravel](https://laravel.com/)を用いたWikiシステムです。

## はじめに

LukiWikiとは、PukiWiki Advance（以下PukiWiki Adv.）で肥大化したコードをLaravelを用いて0から書き直し、MITライセンスで開発するというのが趣旨のプロジェクトです。

## インストール

最初に以下のコマンドを実行し、環境を整えてください。デフォルトでは、DBにMySQL (MariaDB）を使用する設定になっていますが、.envを書き換えてsqliteでも使用可能です。

```sh
composer install
cp .example.env .env
php artisan key:generate
php artisan migrate
```

完了したら`php artisan server`でhttp://localhost:8000/ からプログラムを走らせることができます。

## PukiWikiからのデーター移行方法

※この機能は開発途上です。

LukiWikiは、[PukiWiki](https://pukiwiki,osdn.jp)（UTF-8版のみ）、[PukiWiki Plus!](https://github.com/miko2u/pukiwiki-plus-i18n)、
[PukiWiki Advance](https://pukiwiki.logue.be/)からデータ移行することができます。

まず、PukiWikiのデータディレクトリ（attach、wiki、counterなどのあるディレクトリ）を以下のディレクトリ内に設置します。

```
/lukiwiki/storage/app/[pukiwikiのルートディレクトリ]
```

コマンドラインで以下のように入力し、ジョブキューを常駐させてください。

```
php artisan queue:work
```

次に、<http://localhost:8000/dashboard/convert>にアクセスし、「PukiWikiのデーターの置かれている場所へのパス」にアップロードした場所のパスを入れます。
例えば、`/lukiwiki/storage/app/pukiwiki`にアップした場合、`pukiwiki`と入れます。

ここでは必ず、Wikiデータの移行から行ってください。なお、バックアップデーターは文法変換されずにそのままDBに保存されます。
元データを上書きする処理はありませんが、必ずバックアップを取ってから作業を行ってください。

## ライセンス

[MIT](LISENCE)

## PukiWiki Adv.で起きている問題

以下の理由から派生ではない0ベースのシステムにする必要があった。

### 技術的負債

PukiWiki Adv.では、PukiWikiオリジナルのコードや自作のコードを使わず、Zend Framework2の関数を用いて実装するようにし、
PHPのバージョンアップで動かなくなるリスクをフレームワーク側に肩代わりをすることで、PHPのバージョンアップによって動かなくなるリスクを減らしつつ、
高速化や抽象化をすすめるというのが趣旨だった。

しかし、蓋を開けてみると、PukiWikiとのプラグイン互換性のために命令が増えてしまったり、Zend Frameworkがほとんど広まらなかったため、分かる人がいない状態になってしまっている。

キャッシュ処理などをかなりアグレッシブに行い、高速化をもくろんでいるが、Zend Frameworkそのものが重く、当初の目論見とは大きくかけ離れてたものになってしまっていた。

同じことはクライアントサイドでも言えており、jQueryやjQuery UIやjQuery Mobileなどへの依存が高く、2.2移行のbootstrap移行時に障害になってしまっている。
（当時は、レスポンシブデザインという考えは一般的でなかった）

### ライセンス問題

PukiWikiはGPL2 or laterで開発されており、これが他のライブラリを使用する上で、大きな障害になってしまってる。
前述の通り、PukiWiki Adv.極力外部フレームワークの命令を使い、本体は抽象化を進めるという開発指針との相性が悪く、配布する上でもトラブルになっていた。

これは、PukiWiki Adv.の派生元となったPukiWiki Plus!にも言えている。

2.xからは、オブジェクト指向化を進める上で、コア部分を0から書き直し完全に別物になっているが、プラグインをサポートする上で元のコードを残す必要があったため、
この呪縛に縛られているという意味では変わっていない。

このため、LukiWikiではプラグインの互換性や文法の互換性を取っていない。