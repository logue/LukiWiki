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
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->increments('id')->unsigned()->comment('バックアップ番号');
            $table->integer('post_id')->comment('記事番号')->references('id')->on('pages');
            $table->integer('user_id')->comment('ユーザID');
            $table->longText('source')->comment('内容');
            $table->ipAddress('ip')->comment('編集者のIP');
            $table->timestamps();
        });
        if (\Config::get('database.default') !== 'sqlite') {
            \DB::statement("ALTER TABLE `backups` comment 'バックアップ'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('backups');
    }
}
