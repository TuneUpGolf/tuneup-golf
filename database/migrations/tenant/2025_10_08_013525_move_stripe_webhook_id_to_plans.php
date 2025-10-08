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
            $table->dropColumn([
                'stripe_webhook_id'
            ]);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->string('stripe_webhook_id')->nullable();
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
            $table->string('stripe_webhook_id')->nullable();
        });
        
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_webhook_id'
            ]);
        });
    }
};
