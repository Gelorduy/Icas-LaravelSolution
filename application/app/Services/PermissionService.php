<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Config;

class PermissionService
{
    /**
     * Check if a user has a specific permission
     */
    public static function userHasPermission(User $user, string $permission): bool
    {
        $role = $user->role ?? 'reader';
        $rolePermissions = Config::get("permissions.roles.{$role}.permissions", []);

        // Administrator has wildcard access
        if (in_array('*', $rolePermissions)) {
            return true;
        }

        return in_array($permission, $rolePermissions);
    }

    /**
     * Get all permissions for a user
     */
    public static function getUserPermissions(User $user): array
    {
        $role = $user->role ?? 'reader';
        $permissions = Config::get("permissions.roles.{$role}.permissions", []);

        // If user has wildcard, return all available permissions (map + sidebar)
        if (in_array('*', $permissions)) {
            $mapPermissions = array_values(Config::get('permissions.map_menu', []));
            $sidebarPermissions = array_values(Config::get('permissions.sidebar_menu', []));
            return array_merge($mapPermissions, $sidebarPermissions);
        }

        return $permissions;
    }

    /**
     * Get allowed map menu items for a user
     */
    public static function getAllowedMapMenuItems(User $user): array
    {
        $menuPermissions = Config::get('permissions.map_menu', []);
        $userPermissions = self::getUserPermissions($user);

        $allowedItems = [];
        foreach ($menuPermissions as $menuKey => $requiredPermission) {
            if (in_array($requiredPermission, $userPermissions)) {
                $allowedItems[] = $menuKey;
            }
        }

        return $allowedItems;
    }

    /**
     * Get allowed sidebar menu items for a user
     */
    public static function getAllowedSidebarMenuItems(User $user): array
    {
        $menuPermissions = Config::get('permissions.sidebar_menu', []);
        $userPermissions = self::getUserPermissions($user);

        $allowedItems = [];
        foreach ($menuPermissions as $menuKey => $requiredPermission) {
            if (in_array($requiredPermission, $userPermissions)) {
                $allowedItems[] = $menuKey;
            }
        }

        return $allowedItems;
    }

    /**
     * Get allowed admin menu items for a user
     */
    public static function getAllowedAdminMenuItems(User $user): array
    {
        $menuPermissions = Config::get('permissions.admin_menu', []);
        $userPermissions = self::getUserPermissions($user);

        $allowedItems = [];
        foreach ($menuPermissions as $menuKey => $requiredPermission) {
            // If permission is null, it's available to all authenticated users
            if ($requiredPermission === null || in_array($requiredPermission, $userPermissions)) {
                $allowedItems[] = $menuKey;
            }
        }

        return $allowedItems;
    }
}
