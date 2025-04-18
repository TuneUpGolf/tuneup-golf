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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('instructor_id');
            $table->unsignedBigInteger('lesson_id');
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->string('tenant_id')->nullable();
            $table->unsignedBigInteger('lessons_used')->default('0');
            $table->string('status')->default('incomplete');
            $table->decimal('total_amount', 8, 2)->default(0);
            $table->string('session_id')->nullable();
            $table->timestamps();
            $table->foreign('student_id')->references('id')->on('students');
            $table->foreign('instructor_id')->references('id')->on('users');
            $table->foreign('lesson_id')->references('id')->on('lessons');
            $table->foreign('coupon_id')->references('id')->on('coupons');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchases');
    }
};
