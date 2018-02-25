# LukiWiki

LukiWiki（ルキウィキ）とはLaravelをベースにPukiWiki Advanceを作り直したWikiシステムです。

## 開発方針

* PukiWiki Adv.で肥大化した機能を削減し、よりシンプルに。極力Laravelの機能を使う
* DB対応は検討中。当面はPukiWiki Adv.との互換性重視（storageディレクトリ内にデーターが置かれます。）
* グローバル変数を削除

### 非互換

* プラグインすべて。OOPにするため互換性はありません。
* 設定。前述のグローバル変数を削除するため使えません。
* :から始まるページは、今まで隠しページとして使われていましたが、LukiWikiではコマンドやAPI用に使用するため使用不可能です。

## 使用方法

※WikiをHTMLに変える処理までしか作っていません。

インストール時は以下のコマンドを実行してください。

```ssh
composer install
npm install
```

Laravelの機能でテスト環境を作る際は、サーバーをインストールする必要はありません。以下のコマンドを打って常駐させるだけです。

```ssh
php artisan serve
```

JavaScriptやスタイルシートの修正は、別途以下のコマンドを打って、resources/assets内のファイルを修正してください。直接public/js内のファイルやpublic/css内のファイルはいじらないでください。変更があったタイミングで自動的にコンパイルされます。

```ssh
npm run watch -- --watch-poll
```

## 必要要件

PHP 7.2以上
nodejs

## ライセンス

MIT（予定）

Advの時点で原型を留めないくらいコードを書き直しちゃってますけど、途中からGPLからMITに変えるってどうなんでしょうね？