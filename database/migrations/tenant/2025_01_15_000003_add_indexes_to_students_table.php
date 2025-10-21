<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $tableName = 'students';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            // Email is already unique, but add index for tenant_id for multi-tenancy
            $this->createIndexIfNotExists($table, 'tenant_id', 'students_tenant_id_index');

            // Status and type indexes for filtering
            $this->createIndexIfNotExists($table, 'active_status', 'students_active_status_index');
            $this->createIndexIfNotExists($table, 'type', 'students_type_index');
            $this->createIndexIfNotExists($table, 'isGuest', 'students_isguest_index');

            // Phone indexes for authentication and search
            $this->createIndexIfNotExists($table, 'country_code', 'students_country_code_index');

            // Created by index for admin queries
            $this->createIndexIfNotExists($table, 'created_by', 'students_created_by_index');

            // Verification status indexes
            $this->createIndexIfNotExists($table, 'email_verified_at', 'students_email_verified_at_index');
            $this->createIndexIfNotExists($table, 'phone_verified_at', 'students_phone_verified_at_index');

            // Composite indexes for common queries
            $this->createIndexIfNotExists($table, ['tenant_id', 'active_status'], 'students_tenant_id_active_status_index');
            $this->createIndexIfNotExists($table, ['type', 'active_status'], 'students_type_active_status_index');
            $this->createIndexIfNotExists($table, ['tenant_id', 'type'], 'students_tenant_id_type_index');
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
            $table->dropIndex('students_tenant_id_index');
            $table->dropIndex('students_active_status_index');
            $table->dropIndex('students_type_index');
            $table->dropIndex('students_isguest_index');
            $table->dropIndex('students_country_code_index');
            $table->dropIndex('students_created_by_index');
            $table->dropIndex('students_email_verified_at_index');
            $table->dropIndex('students_phone_verified_at_index');
            $table->dropIndex('students_tenant_id_active_status_index');
            $table->dropIndex('students_type_active_status_index');
            $table->dropIndex('students_tenant_id_type_index');
        });
    }
};
