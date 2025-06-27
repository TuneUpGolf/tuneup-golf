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
        if (!Schema::hasColumn('users', 'payment_country')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('payment_country', ['usa', 'other'])->default('usa');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('users', 'payment_country')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('payment_country');
            });
        }
    }
};
