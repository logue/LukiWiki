# LukiWiki

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/logue/LukiWiki/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/logue/LukiWiki/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/logue/LukiWiki/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/logue/LukiWiki/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/logue/LukiWiki/badges/build.png?b=master)](https://scrutinizer-ci.com/g/logue/LukiWiki/build-status/master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/logue/LukiWiki/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
[![StyleCI](https://github.styleci.io/repos/122809801/shield?branch=master)](https://github.styleci.io/repos/122809801)

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

LukiWikiは、RDBMSにデーターを格納する仕様です。[PukiWiki](https://pukiwiki,osdn.jp)（UTF-8版のみ）、[PukiWiki Plus!](https://github.com/miko2u/pukiwiki-plus-i18n)、
[PukiWiki Advance](https://pukiwiki.logue.be/)からデータ移行することができます。現在のところ、SQLiteとMySQL(MariaDB)5.7以降に対応しています。（※PostgreSQLおよびSQLServerへの対応予定はありますが、未チェックです）

まず、PukiWikiのデータディレクトリ（attach、wiki、counterなどのあるディレクトリ）を以下のディレクトリ内に設置します。

```
/lukiwiki/storage/app/[pukiwikiのルートディレクトリ]
```

コマンドラインで以下のように入力し、ジョブキューを常駐させてください。

```
php artisan queue:work
```

次に、<http://localhost:8000/:dashboard/convert>にアクセスし、「PukiWikiのデーターの置かれている場所へのパス」にアップロードした場所のパスを入れます。
例えば、`/lukiwiki/storage/app/pukiwiki`にアップした場合、`pukiwiki`と入れます。

ここでは必ず、Wikiデータの移行から行ってください。なお、文法変換されるのは、現行のWikiデーターのみでバックアップはそのままDBに保存されます。
元のPukiWikiのデータを上書きする処理はありませんが、念の為バックアップを取ってから作業を行ってください。

なお、複数回実行すると、移行前の内容で上書きされます。

### 仕様

* 差分データー（diffディレクトリ）の移行はせず、バックアップと統合されます。
* 添付ファイルは同一内容の（SHA-1ハッシュの値が等しい）場合、一つのファイルに統合されます。ページに対して添付ファイルが貼り付けられるという仕様は変わりませんが、管理テーブルから同一ファイルを参照するという処理になります。
* 添付ファイルのバックアップの移行はしません。
* InterWikiNameおよび、AutoAliasName、GlossaryはWikiで管理せずに専用のテーブルで管理します。これは管理人のみ編集可能にする予定です。

## プラグインの仕様

概ねPukiWikiと似たような感じになっていますが、オブジェクト指向になっており、必要なパラメータは全てメンバ変数からアクセスします。

プラグインの呼び出し方はPukiWikiと同じですが、明確に()内の値と{}内の値が区別され、また()内の値は自動的に配列として処理されます。
このため、PukiWikiのプラグインの処理でありがちだった、末尾の変数を本文とするみたいな処理は不要となります。

```
@[プラグイン名](パラメータ[,パラメータ2 ...]){本文}
@[プラグイン名](パラメータ[,パラメータ2 ...]){{
本文
}}
&[プラグイン名](パラメータ[,パラメータ2 ...]){本文}
```

* $this->page ページ名
* $this->params パラメーター（,区切りで入力。配列として渡されてくる）
* $this->body 本文、内容。

プラグインの雛形は以下のような感じです。

```php
<?php
/**
 * ダミープラグイン.
 *
 * @author    作者の名前とメールアドレス
 * @copyright 作者の著作権表記
 * @license   ライセンス
 */

namespace App\LukiWiki\Plugins;

use App\LukiWiki\AbstractPlugin;
use App\LukiWiki\BlockPluginInterface;
use App\LukiWiki\InlinePluginInterface;

class Dummy extends AbstractPlugin implements BlockPluginInterface, InlinePluginInterface, ApiPluginInterface
{
    /**
     * インライン型
     *
     * @return string
     */
    public function inline(): string
    {
        return '<span>ダミー</span>';
    }
    /**
     * ブロック型
     *
     * @return string
     */
    public function block(): string
    {
        return '<p>ダミー</p>';
    }
    /**
     * API型
     *
     * @return mixed
     */
    public function api(){
        return ['message'=>'ダミープラグイン'];
    }
    /**
     * コード補完用テキストを出力
     *
     * @return string
     */
    public function syntax(): string
    {
        return '';
    }
    /**
     * プラグインの説明文
     *
     * @return string
     */
    public function usage(): string
    {
        return 'このプラグインはダミーです。単に「ダミー」と出力します。';
    }
}
```
呼び出し時は、PukiWikiと異なり、単にプラグインをディレクトリに入れただけでは認識されません。
Facadeパターンを採用しており、config/lukiwiki.phpのプラグインに以下のように追加する必要があります。

```
    'plugin' => [
        'dummy'      => App\LukiWiki\Plugins\Dummy::class,
    ],
```

このため、プラグインの名前とプラグイン名を一致させる必要はありません。例えば、Abbrプラグインをtooltipで呼び出したい場合は以下のようにします。
同じ変換処理を実行させたい場合は複数定義しても問題ありません。

```
    'plugin' => [
        'abbr'         => App\LukiWiki\Plugins\Abbr::class,
        'tooltip'      => App\LukiWiki\Plugins\Abbr::class,
    ],
```

action型に相当する処理は未実装ですが、似たようなものにAPI型があります。これは```:api/[プラグイン名]:[ページ名]```という形式でアクセスします。
DBの実行結果などをJSONもしくはXMLで返すためのものですが、脆弱性の原因になりかねないので、まだ仕様が確定していません。

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
前述の通り、PukiWiki Adv.極力外部フレームワークの関数を使い、本体の抽象化を進めるという開発指針との相性が悪く、配布する上でもトラブルになっていた。

これは、PukiWiki Adv.の派生元となったPukiWiki Plus!にも言えている。

2.xからは、オブジェクト指向化を進める上で、コア部分を0から書き直し完全に別物になっているが、プラグインをサポートする上で元のコードを残す必要があったため、
この呪縛に縛られているという意味では変わっていない。

このため、LukiWikiではプラグインの互換性や文法の互換性を取っていない。