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
     */
    public function up()
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id')->comment('記事番号');
            $table->unsignedInteger('user_id')->nullable()->references('id')->on('users')->comment('ユーザID');
            $table->string('name', 255)->index()->unique()->comment('ページ名');
            $table->string('title')->nullable()->comment('記事名');
            $table->longText('source')->comment('内容');
            $table->string('description')->nullable()->comment('要約');
            $table->boolean('locked')->default(false)->comment('ロックフラグ');
            $table->unsignedInteger('status')->nullable()->comment('公開状況');
            $table->ipAddress('ip_address')->nullable()->comment('IPアドレス');
            $table->timestamps();   // 更新日／作成日
            $table->softDeletes();  // ソフトデリート
        });
        if (\Config::get('database.default') === 'mysql') {
            \DB::statement('ALTER TABLE ' . \DB::getTablePrefix() . self::TABLE_NAME . ' COMMENT \'' . self::TABLE_COMMENT . '\'');
            // ページ名はBINARY属性を加えて大文字小文字を区別する
            \DB::statement('ALTER TABLE ' . \DB::getTablePrefix() . self::TABLE_NAME . ' MODIFY `name` varchar(255) BINARY');
            // NGRAMでsourceにインデックスをつける
            \DB::statement('ALTER TABLE ' . \DB::getTablePrefix() . self::TABLE_NAME . ' ADD FULLTEXT(`source`)');
        } elseif (\Config::get('database.default') === 'pgsql') {
            \DB::statement('COMMENT ON DATABASE ' . \DB::getTablePrefix() . self::TABLE_NAME . ' IS \'' . self::TABLE_COMMENT . '\'');
            // NGRAMでsourceにインデックスを付ける
            \DB::statement('CREATE INDEX source_idx ON ' . \DB::getTablePrefix() . self::TABLE_NAME . ' USING gin (source gin_trgm_ops);');
        } elseif (\Config::get('database.default') === 'sqlserv') {
            \DB::statement('EXEC sys.sp_addextendedproperty  @name=N\'MS_Description\',@value=N\'' . self::TABLE_COMMENT . '\',@level0type=N\'SCHEMA\',@level0name=N\'dbo\',@level1type=N\'TABLE\',@level1name=N\'' . \DB::getTablePrefix() . self::TABLE_NAME . '\'');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}
