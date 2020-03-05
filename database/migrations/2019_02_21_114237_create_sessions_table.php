<?php

/**
 * セッション管理テーブル作成.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionsTable extends Migration
{
    const TABLE_NAME = 'sessions';
    const TABLE_COMMENT = 'セッション';

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->string('id')->unique()->comment('セッションID');
            $table->unsignedInteger('user_id')->nullable()->references('id')->on('users')->comment('ユーザID');
            $table->ipAddress('ip_address')->nullable()->comment('IPアドレス');
            $table->text('user_agent')->nullable()->comment('ユーザエージェント');
            $table->text('payload')->comment('セッション内容');
            $table->integer('last_activity')->comment('行動フラグ');
        });
        if (\Config::get('database.default') === 'mysql') {
            \DB::statement('ALTER TABLE ' . \DB::getTablePrefix() . self::TABLE_NAME . ' COMMENT \'' . self::TABLE_COMMENT . '\'');
        } elseif (\Config::get('database.default') === 'pgsql') {
            \DB::statement('COMMENT ON DATABASE ' . \DB::getTablePrefix() . self::TABLE_NAME . ' IS \'' . self::TABLE_COMMENT . '\'');
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
