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
        Schema::table('lessons', function (Blueprint $table) {
            $table->integer('advance_booking_limit_days')->nullable();
            $table->integer('last_minute_booking_buffer_hours')->nullable();
            $table->integer('cancel_window_hours')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn([
                'advance_booking_limit_days',
                'last_minute_booking_buffer_hours',
                'cancel_window_hours',
            ]);
        });
    }
};
