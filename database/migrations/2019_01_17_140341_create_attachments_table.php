<?php
/**
 * 添付ファイル管理テーブル作成.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttachmentsTable extends Migration
{
    const TABLE_NAME = 'attachements';
    const TABLE_COMMENT = '添付ファイル管理';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->increments('id')->unsigned()->comment('ファイル番号');
            $table->integer('page_id')->unsigned()->references('id')->on('pages')->comment('記事ID');
            $table->integer('user_id')->unsigned()->references('id')->on('users')->comment('ユーザID');
            $table->integer('attachment_id')->unsigned()->nullable()->comment('元ファイルのID');    // バックアップ用途
            $table->string('name')->comment('ファイル名');
            $table->string('path')->comment('実体へのパス');
            $table->string('mime')->default('application/octet-stream')->comment('MIMEタイプ');
            $table->ipAddress('ip')->comment('投稿者のIP');
            $table->boolean('locked')->comment('ロックフラグ');
            $table->integer('size')->comment('ファイル容量');       // バイトで管理（1G前後ファイルを添付することは想像したくないが・・・。）
            $table->string('hash', 32)->comment('ハッシュ');        // Sha-256で管理。改ざん対策。
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
