<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('super_admin_instructors', function (Blueprint $table) {
            $table->id();
            $table->string('instructor_image')->nullable(); // image upload field (store path)
            $table->text('bio')->nullable(); // bio textarea field
            $table->string('domain')->nullable(); // domain textbox
            $table->softDeletes(); // adds deleted_at for soft deletes
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('super_admin_instructors');
    }
};
