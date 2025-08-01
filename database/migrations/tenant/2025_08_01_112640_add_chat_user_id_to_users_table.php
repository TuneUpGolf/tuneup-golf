<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('chat_user_id')->nullable()->after('id');
            $table->string('group_id')->nullable();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('chat_user_id')->nullable()->after('id');
            $table->string('group_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('chat_user_id');
            $table->dropColumn('group_id');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('chat_user_id');
            $table->dropColumn('group_id');
        });
    }
};
