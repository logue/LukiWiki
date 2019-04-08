<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSocialiteFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('provider_name')->nullable()->after('id');
            $table->string('provider_id')->nullable()->after('provider_name');
            $table->string('password')->nullable()->change();
            $table->string('avatar')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (\Config::get('database.default') === 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('provider_name');
            });
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('provider_id');
            });
            Schema::table('users', function (Blueprint $table) {
                $table->string('password')->nullable(false)->change();
            });
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('avatar');
            });
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('provider_name');
                $table->dropColumn('provider_id');
                $table->string('password')->nullable(false)->change();
                $table->dropColumn('avatar');
            });
        }
    }
}
