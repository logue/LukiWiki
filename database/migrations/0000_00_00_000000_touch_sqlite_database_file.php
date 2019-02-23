<?php
/**
 * Sqliteを使用する設定だった場合、データベースファイルを作成.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */
use Illuminate\Database\Migrations\Migration;

class TouchSqliteDatabaseFile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (\Config::get('database.default') === 'sqlite' && !\File::exists(\Config::get('database.sqlite.database'))) {
            touch(\Config::get('database.sqlite.database'));
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (\Config::get('database.default') === 'sqlite') {
            \File::delete(\Config::get('database.sqlite.database'));
        }
    }
}
