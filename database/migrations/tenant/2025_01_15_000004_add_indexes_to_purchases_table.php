<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $tableName = 'purchases';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            // Foreign key indexes for better join performance
            $this->createIndexIfNotExists($table, 'student_id', 'purchases_student_id_index');
            $this->createIndexIfNotExists($table, 'instructor_id', 'purchases_instructor_id_index');
            $this->createIndexIfNotExists($table, 'lesson_id', 'purchases_lesson_id_index');
            $this->createIndexIfNotExists($table, 'coupon_id', 'purchases_coupon_id_index');
            $this->createIndexIfNotExists($table, 'slot_id', 'purchases_slot_id_index');

            // Status and type indexes for filtering
            $this->createIndexIfNotExists($table, 'status', 'purchases_status_index');
            $this->createIndexIfNotExists($table, 'type', 'purchases_type_index');
            $this->createIndexIfNotExists($table, 'isFeedbackComplete', 'purchases_isfeedbackcomplete_index');

            // Amount and quantity indexes for reporting
            $this->createIndexIfNotExists($table, 'total_amount', 'purchases_total_amount_index');
            $this->createIndexIfNotExists($table, 'purchased_slot', 'purchases_purchased_slot_index');
            $this->createIndexIfNotExists($table, 'lessons_used', 'purchases_lessons_used_index');

            // Timestamp indexes for sorting and filtering
            $this->createIndexIfNotExists($table, 'created_at', 'purchases_created_at_index');
            $this->createIndexIfNotExists($table, 'updated_at', 'purchases_updated_at_index');

            // Composite indexes for common queries
            $this->createIndexIfNotExists($table, ['student_id', 'status'], 'purchases_student_id_status_index');
            $this->createIndexIfNotExists($table, ['instructor_id', 'status'], 'purchases_instructor_id_status_index');
            $this->createIndexIfNotExists($table, ['lesson_id', 'status'], 'purchases_lesson_id_status_index');
            $this->createIndexIfNotExists($table, ['tenant_id', 'status'], 'purchases_tenant_id_status_index');
            $this->createIndexIfNotExists($table, ['student_id', 'created_at'], 'purchases_student_id_created_at_index');
            $this->createIndexIfNotExists($table, ['instructor_id', 'created_at'], 'purchases_instructor_id_created_at_index');
        });
    }

    /**
     * Create index if it doesn't exist
     */
    private function createIndexIfNotExists(Blueprint $table, $columns, $indexName)
    {
        try {
            $table->dropIfExistsIndex($indexName);
            $table->index($columns, $indexName);
        } catch (\Exception $e) {
            // Index might already exist, continue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropIndex('purchases_student_id_index');
            $table->dropIndex('purchases_instructor_id_index');
            $table->dropIndex('purchases_lesson_id_index');
            $table->dropIndex('purchases_coupon_id_index');
            $table->dropIndex('purchases_slot_id_index');
            $table->dropIndex('purchases_status_index');
            $table->dropIndex('purchases_type_index');
            $table->dropIndex('purchases_isfeedbackcomplete_index');
            $table->dropIndex('purchases_total_amount_index');
            $table->dropIndex('purchases_purchased_slot_index');
            $table->dropIndex('purchases_lessons_used_index');
            $table->dropIndex('purchases_created_at_index');
            $table->dropIndex('purchases_updated_at_index');
            $table->dropIndex('purchases_student_id_status_index');
            $table->dropIndex('purchases_instructor_id_status_index');
            $table->dropIndex('purchases_lesson_id_status_index');
            $table->dropIndex('purchases_tenant_id_status_index');
            $table->dropIndex('purchases_student_id_created_at_index');
            $table->dropIndex('purchases_instructor_id_created_at_index');
        });
    }
};
