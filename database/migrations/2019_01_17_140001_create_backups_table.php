<?php
/**
 * バックアップデータ格納テーブル.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBackupsTable extends Migration
{
    const TABLE_NAME = 'backups';
    const TABLE_COMMENT = 'バックアップ';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->increments('id')->unsigned()->comment('バックアップ番号');
            $table->integer('post_id')->unsigned()->references('id')->on('pages')->comment('記事番号');
            $table->integer('user_id')->unsigned()->references('id')->on('users')->comment('ユーザID');
            $table->longText('source')->comment('内容');
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
