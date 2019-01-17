<?php
/**
 * パスワード初期化テーブル.
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
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index()->comment('メールアドレス');
            $table->string('token')->comment('トークン');
            $table->timestamp('created_at')->nullable();
        });
        if (\Config::get('database.default') !== 'sqlite') {
            \DB::statement("ALTER TABLE `password_resets` comment 'パスワード初期化'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('password_resets');
    }
}
