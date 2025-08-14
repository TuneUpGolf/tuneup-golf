<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $tableName = 'slots';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            // Foreign key indexes for better join performance
            $this->createIndexIfNotExists($table, 'lesson_id', 'slots_lesson_id_index');

            // Date/time index for filtering and sorting
            $this->createIndexIfNotExists($table, 'date_time', 'slots_date_time_index');

            // Status indexes for filtering
            $this->createIndexIfNotExists($table, 'is_completed', 'slots_is_completed_index');
            $this->createIndexIfNotExists($table, 'is_active', 'slots_is_active_index');
            $this->createIndexIfNotExists($table, 'cancelled', 'slots_cancelled_index');

            // Composite index for common queries
            $this->createIndexIfNotExists($table, ['lesson_id', 'date_time'], 'slots_lesson_id_date_time_index');
            $this->createIndexIfNotExists($table, ['is_active', 'date_time'], 'slots_is_active_date_time_index');
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
            $table->dropIndex('slots_lesson_id_index');
            $table->dropIndex('slots_date_time_index');
            $table->dropIndex('slots_is_completed_index');
            $table->dropIndex('slots_is_active_index');
            $table->dropIndex('slots_cancelled_index');
            $table->dropIndex('slots_lesson_id_date_time_index');
            $table->dropIndex('slots_is_active_date_time_index');
        });
    }
};
