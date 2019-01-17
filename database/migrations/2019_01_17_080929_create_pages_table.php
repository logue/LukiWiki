<?php
/**
 * ページデーター格納テーブル.
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
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->increments('id')->unsigned()->comment('記事番号');
            $table->integer('user_id')->comment('ユーザID')->default(0);
            $table->string('title')->unique()->comment('記事名');
            $table->longText('source')->comment('内容');
            $table->boolean('locked')->comment('ロックフラグ')->default(false);
            $table->integer('status')->comment('公開状況')->default(0);
            $table->ipAddress('ip')->comment('編集者のIP');
            $table->timestamps();
        });
        if (\Config::get('database.default') !== 'sqlite') {
            \DB::statement("ALTER TABLE `pages` comment 'ページ情報'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pages');
    }
}
