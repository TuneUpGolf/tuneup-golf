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
        Schema::table('album_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('album_categories', 'column_order')) {
                $table->integer('column_order')->default(1)->after('id');
            }
        });
        $plans = DB::table('album_categories')->get();
        foreach ($plans as $index => $plan) {
            DB::table('album_categories')
                ->where('id', $plan->id)
                ->update(['column_order' => $index + 1]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {});
    }
};
