<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lessons', function () {
            DB::statement("ALTER TABLE lessons MODIFY lesson_description LONGTEXT NULL");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lessons', function () {
            DB::statement("ALTER TABLE lessons MODIFY lesson_description TEXT NULL");
        });
    }
};
