<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $tableName = 'student_slots';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            // Foreign key indexes for better join performance
            $this->createIndexIfNotExists($table, 'slot_id', 'student_slots_slot_id_index');
            $this->createIndexIfNotExists($table, 'student_id', 'student_slots_student_id_index');

            // Composite index for common queries (finding all slots for a student)
            $this->createIndexIfNotExists($table, ['student_id', 'slot_id'], 'student_slots_student_id_slot_id_index');

            // Index for friend-related queries
            $this->createIndexIfNotExists($table, 'isFriend', 'student_slots_isfriend_index');

            // Timestamp indexes for sorting and filtering
            $this->createIndexIfNotExists($table, 'created_at', 'student_slots_created_at_index');
            $this->createIndexIfNotExists($table, 'updated_at', 'student_slots_updated_at_index');
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
            $table->dropIndex('student_slots_slot_id_index');
            $table->dropIndex('student_slots_student_id_index');
            $table->dropIndex('student_slots_student_id_slot_id_index');
            $table->dropIndex('student_slots_isfriend_index');
            $table->dropIndex('student_slots_created_at_index');
            $table->dropIndex('student_slots_updated_at_index');
        });
    }
};
