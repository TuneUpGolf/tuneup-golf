<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permission = Permission::firstOrCreate(['name' => 'manage-email-template']);

        $instructorRole = Role::where('name', 'Instructor')->first();

        if ($instructorRole) {
            $instructorRole->givePermissionTo($permission);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $instructorRole = Role::where('name', 'Instructor')->first();
        $permission = Permission::where('name', 'manage-email-template')->first();

        if ($instructorRole && $permission) {
            $instructorRole->revokePermissionTo($permission);
        }
    }
};
