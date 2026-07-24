<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewPermissions extends Model
{
    protected $table = 'view_access_permission_per_role';

    /** @var array<int, array<int, string>> */
    protected static array $rolePermissionCache = [];

    /**
     * Permission route names granted to a role (queries role_permission directly).
     */
    public static function permissionNamesForRole(int|string $roleId): array
    {
        $roleId = (int) $roleId;

        if (array_key_exists($roleId, self::$rolePermissionCache)) {
            return self::$rolePermissionCache[$roleId];
        }

        self::$rolePermissionCache[$roleId] = Permission::query()
            ->join('role_permission', 'permission.id', '=', 'role_permission.permission_id')
            ->where('role_permission.role_id', $roleId)
            ->where('role_permission.isAllowed', 1)
            ->whereNull('permission.deleted_at')
            ->pluck('permission.name')
            ->all();

        return self::$rolePermissionCache[$roleId];
    }

    public static function check_permission($role_id, $action)
    {
        $user = auth()->user();
        if ($user && $user->is_an_admin()) {
            return 1;
        }

        $permissions = self::permissionNamesForRole($role_id);

        return in_array($action, $permissions, true) ? 1 : 0;
    }
}
