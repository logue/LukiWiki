<?php
/**
 * パスワード初期化テーブル作成.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2019 Logue
 * @license   MIT
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePasswordResetsTable extends Migration
{
    const TABLE_NAME = 'password_resets';
    const TABLE_COMMENT = 'パスワード初期化管理';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->string('email')->index()->comment('メールアドレス');
            $table->string('token')->comment('トークン');
            $table->timestamp('created_at')->nullable();
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
