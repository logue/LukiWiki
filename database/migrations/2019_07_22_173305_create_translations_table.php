<?php

/**
 * 翻訳テーブル作成.
 *
 * @author    Logue <logue@hotmail.co.jp>
 * @copyright 2020 Logue
 * @license   MIT
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ltm_translations', function (Blueprint $table) {
            $table->collation = 'utf8mb4_bin';
            $table->bigIncrements('id');
            $table->integer('status')->default(0);
            $table->string('locale');
            $table->string('group');
            $table->text('key');
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('ltm_translations');
    }
}
