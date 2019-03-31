<?php
/**
 * InterWiki、AutoAlias、Glossalyの定義テーブル.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreareInterwikisTable extends Migration
{
    const TABLE_NAME = 'interwikis';
    const TABLE_COMMENT = 'InterWiki';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('名前');
            $table->string('value')->comment('値');
            $table->integer('type')->nullable()->comment('種別');
            $table->string('encode')->nullable()->comment('エンコード');
            $table->boolean('enabled')->default(1)->comment('有効か');
            $table->ipAddress('ip_address')->nullable()->comment('IPアドレス');
            $table->timestamps();
        });
        if (\Config::get('database.default') === 'mysql') {
            \DB::statement('ALTER TABLE '.\DB::getTablePrefix().self::TABLE_NAME.' COMMENT \''.self::TABLE_COMMENT.'\'');
            // 名前はBINARY属性を加えて大文字小文字を区別する
            \DB::statement('ALTER TABLE '.\DB::getTablePrefix().self::TABLE_NAME.' MODIFY `name` varchar(255) BINARY');
        } elseif (\Config::get('database.default') === 'pgsql') {
            \DB::statement('COMMENT ON DATABASE '.\DB::getTablePrefix().self::TABLE_NAME.' IS \''.self::TABLE_COMMENT.'\'');
        } elseif (\Config::get('database.default') === 'sqlserv') {
            \DB::statement('EXEC sys.sp_addextendedproperty  @name=N\'MS_Description\',@value=N\''.self::TABLE_COMMENT.'\',@level0type=N\'SCHEMA\',@level0name=N\'dbo\',@level1type=N\'TABLE\',@level1name=N\''.\DB::getTablePrefix().self::TABLE_NAME.'\'');
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
