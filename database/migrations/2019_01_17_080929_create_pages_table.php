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
            $table->increments('id')->unsigned()->comment('記事番号');
            $table->integer('user_id')->references('id')->on('paguserses')->comment('ユーザID');
            $table->string('title')->unique()->comment('記事名');
            $table->longText('source')->comment('内容');
            $table->boolean('locked')->comment('ロックフラグ');
            $table->integer('status')->unsigned()->comment('公開状況');
            $table->ipAddress('ip')->comment('編集者のIP');
            $table->timestamps();
        });
        if (\Config::get('database.default') !== 'sqlite') {
            \DB::statement('ALTER TABLE '.DB::getTablePrefix().self::TABLE_NAME.' comment \''.self::TABLE_COMMENT.'\'');
        }
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
