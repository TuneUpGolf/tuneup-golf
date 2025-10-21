<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        Schema::table('lessons', function (Blueprint $table) {
            if (!Schema::hasColumn('lessons', 'column_order')) {
                $table->integer('column_order')->default(1)->after('id');
            }
        });

        // Populate column_order for existing rows

    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lessons', function (Blueprint $table) {
            //
        });
    }
};
