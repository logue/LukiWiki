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
            $table->unsignedInteger('user_id')->nullable()->references('id')->on('users')->comment('ユーザID');
            $table->string('name')->index()->comment('ページ名');
            $table->string('title')->nullable()->comment('記事名');
            $table->longText('source')->comment('内容');
            $table->string('description')->nullable()->comment('要約');
            $table->boolean('locked')->default(false)->comment('ロックフラグ');
            $table->unsignedInteger('status')->nullable()->comment('公開状況');
            $table->ipAddress('ip_address')->nullable()->comment('IPアドレス');
            $table->timestamps();   // 更新日／作成日
            $table->softDeletes();  // ソフトデリート
        });
        if (\Config::get('database.default') !== 'sqlite') {
            \DB::statement('ALTER TABLE '.\DB::getTablePrefix().self::TABLE_NAME.' comment \''.self::TABLE_COMMENT.'\'');
        }
        // ページ名はBINARY属性を加えて大文字小文字を区別する
        \DB::statement('ALTER TABLE '.\DB::getTablePrefix().self::TABLE_NAME.' MODIFY name varchar BINARY');
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
