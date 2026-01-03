<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;

class RoleManagementController extends Controller
{
    public function index()
    {
        abort_unless(PermissionService::userHasPermission(auth()->user(), 'admin.roles.manage'), 403);

        $roles = collect(config('permissions.roles', []))->map(function ($roleData, $roleKey) {
            return [
                'key' => $roleKey,
                'permissions' => $roleData['permissions'] ?? [],
            ];
        })->values();

        $availablePermissions = $this->getAllAvailablePermissions();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'availablePermissions' => $availablePermissions,
        ]);
    }

    private function getAllAvailablePermissions()
    {
        $mapPermissions = array_values(config('permissions.map_menu', []));
        $sidebarPermissions = array_values(config('permissions.sidebar_menu', []));
        $adminPermissions = array_values(config('permissions.admin_menu', []));

        $allPermissions = array_merge($mapPermissions, $sidebarPermissions, $adminPermissions);
        
        // Filter out nulls and get unique permissions
        $allPermissions = array_filter($allPermissions, fn($perm) => $perm !== null);
        
        return array_values(array_unique($allPermissions));
    }
}

