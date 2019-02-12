<?php
/**
 * ページデーター格納テーブル作成.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagesTable extends Migration
{
    const TABLE_NAME = 'pages';
    const TABLE_COMMENT = 'ページ情報';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id')->comment('記事番号');
            $table->unsignedInteger('user_id')->references('id')->on('paguserses')->comment('ユーザID');
            $table->string('name')->index()->comment('ページ名');
            $table->string('title')->nullable()->comment('記事名');
            $table->longText('source')->comment('内容');
            $table->string('description')->nullable()->comment('要約');
            $table->boolean('locked')->default(false)->comment('ロックフラグ');
            $table->unsignedInteger('status')->default('0')->comment('公開状況');
            $table->ipAddress('ip')->comment('編集者のIP');
            $table->timestamps();
            $table->softDeletes();  // ソフトデリート
        });
        if (\Config::get('database.default') !== 'sqlite') {
            \DB::statement('ALTER TABLE '.\DB::getTablePrefix().self::TABLE_NAME.' comment \''.self::TABLE_COMMENT.'\'');
        }
        DB::table('pages')->insert([
            'name'    => 'MainPage',
            'user_id' => 0,
            'ip'      => '127.0.0.1',
            'source'  => '# Welcome to LukiWiki

インストール成功おめでとうございます。この画面が正常に表示されているということは、インストールに成功したということです。
まずは、[SandBox]で行きLukiWikiの機能を試してみましょう。

実働環境に置く前に、.envの書き換えを行ってください。

# カスタマイズ

- [MainPage] - このページです。
- [SideBar] - サイドメニューを定義します

# サポート

- [プロジェクトサイト](https://github.com/logue/LukiWiki)
 - [問題報告](https://github.com/logue/LukiWiki/issues)
- [Twitter](https://twitter.com/pukiwiki_adv)
',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
