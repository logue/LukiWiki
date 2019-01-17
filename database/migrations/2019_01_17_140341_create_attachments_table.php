<?php
/**
 * 添付ファイル管理テーブル.
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
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->increments('id')->unsigned()->comment('ファイル番号');
            $table->integer('page_id')->comment('記事ID')->references('id')->on('pages');
            $table->integer('user_id')->comment('ユーザID')->default(0);
            $table->integer('attachment_id')->comment('元ファイルのID')->nullable();    // バックアップ用途
            $table->string('name')->comment('ファイル名');
            $table->string('path')->comment('実体へのパス');
            $table->string('mime')->comment('MIMEタイプ')->default('application/octet-stream');
            $table->ipAddress('ip')->comment('投稿者のIP');
            $table->boolean('locked')->comment('ロックフラグ')->default(false);
            $table->integer('size')->comment('ファイル容量');       // バイトで管理（1G前後ファイルを添付することは想像したくないが・・・。）
            $table->string('hash', 32)->comment('ハッシュ');       // Sha-256で管理。改ざん対策
            $table->timestamps();
        });
        if (\Config::get('database.default') !== 'sqlite') {
            \DB::statement("ALTER TABLE `attachments` comment '添付ファイル管理'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attachments');
    }
}
