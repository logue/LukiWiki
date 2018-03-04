# LukiWiki

LukiWiki（ルキウィキ）とはLaravelをベースに[PukiWiki Advance](https://github.com/logue/pukiwiki_adv)を作り直したWikiシステムです。

サンプルサイト：<https://lukiwiki.logue.be>　※上がっているソースと同じとは限りません。

## 開発方針

* PukiWiki Adv.で肥大化した機能を削減し、よりシンプルに。極力Laravelの機能を使う
* DB対応は検討中。当面はPukiWiki Adv.との互換性重視（storageディレクトリ内にデーターが置かれます。）
* グローバル変数を削除

### 非互換

* プラグインすべて。OOPにするため互換性はありません。PukiWiki Adv.と異なりラッパーを用意する予定もありません。作り直しになります。まだ仕様は決まっていませんが原則的にこのAbstractPluginクラスに則る形になると思います。composerや
* 設定ファイル。前述のグローバル変数を削除するため使えません。設定項目は大幅に少なくなる予定です。

## 試験方法

インストール時は以下のコマンドを実行してください。

```ssh
composer install
npm install
```

テスト環境を作る際は、PHPの機能とLaravelの機能でサーバーをインストールする必要はありません。以下のコマンドを打って常駐させるだけです。

```ssh
php artisan serve
```

また、JavaScriptやスタイルシートの修正は、別途以下のコマンドを打って、resources/assets内のファイルを修正してください。
直接public/js内のファイルやpublic/css内のファイルはいじらないでください。
このコマンドを常駐させている間は変更があったタイミングで自動的にコンパイルされます。

```ssh
npm run watch -- --watch-poll
```

## お願い

* アイデアなどがありましたら、Githubの[Issues](https://github.com/logue/LukiWiki/issues)に登録してください。
* コーディングルールは同封の[.php-cs](https://github.com/logue/LukiWiki/blob/master/.php_cs)となっています。コミット前や投稿前に`php-cs-fixer fix .`を実行してください。
* 現在のところ推奨エディタは、OSを問わず[Visual Studio Code](https://code.visualstudio.com/)です。一部の設定は、プロジェクトに含まれています。

## 主な変更点

* 文法などは原則的にPukiWiki Adv.準拠だが大幅に削減。
* 顔文字やハートなどはUnicodeに標準で含まれるようになったため削除
* ページ名は`REQUEST_URI`からのみ取り、Query Stringは使わない。
* 凍結（freeze）はロック（Lock）に変更
* FrontPageはMainPageに変更
* シンタックスハイライトの処理とテキストエリアの処理は[CodeMirror](https://codemirror.net/)で統一。
* MenuBarはSideBarに変更だが、サイドバーをWikiで実装するメリットがあるのか微妙なので保留中。後述のAMP対応などの事情があるため管理画面からカスタマイズという形にする予定。
* jQuery Mobile廃止し代わりに[AMP（Accelerated Mobile Pages）](https://www.ampproject.org/ja/)で代用。各ページで?action=ampパラメータを付けることで表示される予定です。
* ユーザ管理はすべて廃止。代わりにLaravelの任意のモジュールで実装。SNSログイン前提。
* データーのやり取りは[File](https://github.com/logue/pukiwiki_adv/blob/master/wiki-common/lib/PukiWiki/File/AbstractFile.php)クラスに変わって、[WikiFileSystem](https://github.com/logue/LukiWiki/blob/master/app/LukiWiki/Utility/WikiFileSystem.php)クラスで行います。極力マジックメソッドで実装するようにしているため、Wikiのページをあたかも変数のように使用可能です。
* スパムの温床になったりHTTPS化で正確なパラメータが取れないため、トラックバック、アクセス解析、PingBack機能はすべて廃止。Google Analyticsを使用してください。

## TODO

* 添付ファイルの管理をどうするか？WordpressやMediaWikiのように全体で管理するか、それともこれまでのようにページ単位で管理するか。
* バックアップの仕様。予定ではページごとにSqliteで管理予定。
* LaravelじゃなくLumenでもいいんじゃね？

## 必要要件

* PHP 7.2以上
* nodejs

## ライセンス

MIT

Advの時点で原型を留めないくらいコードを書き直しちゃってますけど、途中からGPLからMITに変えるってどうなんでしょうね？