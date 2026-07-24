<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('role_permission') || ! Schema::hasTable('permission')) {
            return;
        }

        DB::statement('DROP VIEW IF EXISTS view_access_permission_per_role');
        DB::statement('DROP VIEW IF EXISTS view_role_permission');

        DB::statement("CREATE VIEW view_role_permission AS
            SELECT role_permission.user_id,
                   role_permission.role_id AS role,
                   permission.name,
                   permission.module AS permission_module
            FROM role_permission
            INNER JOIN permission ON role_permission.permission_id = permission.id
            WHERE role_permission.isAllowed = 1");

        DB::statement("CREATE VIEW view_access_permission_per_role AS
            SELECT user_id,
                   role,
                   GROUP_CONCAT(name SEPARATOR '|') AS permissions
            FROM view_role_permission
            GROUP BY user_id, role");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS view_access_permission_per_role');
        DB::statement('DROP VIEW IF EXISTS view_role_permission');
    }
};
