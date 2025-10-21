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
        Schema::create('student_subscription_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('student_subscription_id');
            $table->unsignedInteger('invoice_id');
            $table->unsignedInteger('payment_intent_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_subscription_details');
    }
};
