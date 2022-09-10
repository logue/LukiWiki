<?php

/**
 * カウンターテーブル作成.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCountersTable extends Migration
{
    const TABLE_NAME = 'counters';

    const TABLE_COMMENT = 'カウンタ';

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('page_id')->on('pages')->onDelete('cascade')->comment('記事ID');
            $table->unsignedInteger('total')->default(0)->comment('カウンタ');
            $table->unsignedInteger('today')->default(0)->comment('本日のカウンタ');
            $table->unsignedInteger('yesterday')->default(0)->comment('昨日のカウンタ');
            $table->ipAddress('ip_address')->nullable()->comment('IPアドレス');
            $table->datetime('updated_at')->nullable()->comment('最終アクセス日時');
        });
        if (\Config::get('database.default') === 'mysql') {
            \DB::statement('ALTER TABLE '.\DB::getTablePrefix().self::TABLE_NAME.' COMMENT \''.self::TABLE_COMMENT.'\'');
        } elseif (\Config::get('database.default') === 'pgsql') {
            \DB::statement('COMMENT ON DATABASE '.\DB::getTablePrefix().self::TABLE_NAME.' IS \''.self::TABLE_COMMENT.'\'');
        } elseif (\Config::get('database.default') === 'sqlserv') {
            \DB::statement('EXEC sys.sp_addextendedproperty  @name=N\'MS_Description\',@value=N\''.self::TABLE_COMMENT.'\',@level0type=N\'SCHEMA\',@level0name=N\'dbo\',@level1type=N\'TABLE\',@level1name=N\''.\DB::getTablePrefix().self::TABLE_NAME.'\'');
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
