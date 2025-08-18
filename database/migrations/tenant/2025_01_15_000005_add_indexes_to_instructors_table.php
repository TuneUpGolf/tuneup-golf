<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $tableName = 'instructors';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            // Email is already unique, but add index for tenant_id for multi-tenancy
            $this->createIndexIfNotExists($table, 'tenant_id', 'instructors_tenant_id_index');

            // Status and type indexes for filtering
            $this->createIndexIfNotExists($table, 'active_status', 'instructors_active_status_index');
            $this->createIndexIfNotExists($table, 'type', 'instructors_type_index');

            // Country and dial code indexes
            $this->createIndexIfNotExists($table, 'country_code', 'instructors_country_code_index');
            $this->createIndexIfNotExists($table, 'dial_code', 'instructors_dial_code_index');

            // Created by index for admin queries
            $this->createIndexIfNotExists($table, 'created_by', 'instructors_created_by_index');

            // Verification status indexes
            $this->createIndexIfNotExists($table, 'email_verified_at', 'instructors_email_verified_at_index');
            $this->createIndexIfNotExists($table, 'phone_verified_at', 'instructors_phone_verified_at_index');

            // Composite indexes for common queries
            $this->createIndexIfNotExists($table, ['tenant_id', 'active_status'], 'instructors_tenant_id_active_status_index');
            $this->createIndexIfNotExists($table, ['type', 'active_status'], 'instructors_type_active_status_index');
            $this->createIndexIfNotExists($table, ['tenant_id', 'type'], 'instructors_tenant_id_type_index');
            $this->createIndexIfNotExists($table, ['active_status', 'type'], 'instructors_active_status_type_index');
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
            $table->dropIndex('instructors_tenant_id_index');
            $table->dropIndex('instructors_active_status_index');
            $table->dropIndex('instructors_type_index');
            $table->dropIndex('instructors_country_code_index');
            $table->dropIndex('instructors_dial_code_index');
            $table->dropIndex('instructors_created_by_index');
            $table->dropIndex('instructors_email_verified_at_index');
            $table->dropIndex('instructors_phone_verified_at_index');
            $table->dropIndex('instructors_tenant_id_active_status_index');
            $table->dropIndex('instructors_type_active_status_index');
            $table->dropIndex('instructors_tenant_id_type_index');
            $table->dropIndex('instructors_active_status_type_index');
        });
    }
};
