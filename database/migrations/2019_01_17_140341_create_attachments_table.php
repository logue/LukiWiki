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
    const TABLE_NAME = 'attachments';

    const TABLE_COMMENT = '添付ファイル管理';

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(self::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id')->comment('ファイル番号');
            $table->unsignedBigInteger('page_id')->references('id')->on('pages')->onDelete('cascade')->comment('記事ID');
            $table->unsignedBigInteger('user_id')->nullable()->references('id')->on('users')->comment('ユーザID');
            $table->string('name', 255)->comment('ファイル名');
            $table->string('stored_name')->comment('実体名');
            $table->string('mime')->default('application/octet-stream')->comment('MIMEタイプ');
            $table->ipAddress('ip_address')->nullable()->comment('IPアドレス');
            $table->boolean('locked')->default(false)->comment('ロックフラグ');
            $table->unsignedInteger('size')->comment('ファイル容量');   // バイトで管理（1G前後ファイルを添付することは想像したくないが・・・。）
            $table->unsignedInteger('count')->default(0)->comment('カウンタ');
            $table->json('meta')->nullable()->comment('メタ情報');
            $table->timestamps();
        });
        if (\Config::get('database.default') === 'mysql') {
            \DB::statement('ALTER TABLE '.\DB::getTablePrefix().self::TABLE_NAME.' COMMENT \''.self::TABLE_COMMENT.'\'');
            // ファイル名は、BINARY属性を加えて大文字小文字を区別する
            \DB::statement('ALTER TABLE '.\DB::getTablePrefix().self::TABLE_NAME.' MODIFY `name` varchar(255) BINARY');
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

        // 添付ファイルディレクトリとサムネイルディレクトリを初期化する
        $dirs = [\Config::get('lukiwiki.directory.attach'), \Config::get('lukiwiki.directory.thumb')];

        foreach ($dirs as $dir) {
            $gitignore = storage_path('app'.DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR.'.gitignore');
            // .gitignoreは退避
            if (\File::exists($gitignore)) {
                \File::move($gitignore, storage_path('.gitignore'));
                // 初期化
                \File::cleanDirectory(storage_path('app'.DIRECTORY_SEPARATOR.$dir));
                // 戻す
                \File::move(storage_path('.gitignore'), $gitignore);
            } else {
                \File::cleanDirectory(storage_path('app'.DIRECTORY_SEPARATOR.$dir));
            }
        }
    }
}
