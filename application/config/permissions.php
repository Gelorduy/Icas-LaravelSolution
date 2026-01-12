<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Role Permissions
    |--------------------------------------------------------------------------
    |
    | Define which roles have access to specific map features and menu items.
    | The 'administrator' role has access to all features by default.
    |
    */

    'roles' => [
        'administrator' => [
            'permissions' => ['*'], // Wildcard grants all permissions
        ],
        'operator' => [
            'permissions' => [
                'map.actions',
                'map.layers',
                'map.viewports',
                'map.maps',
                'map.options',
                'sidebar.dashboard',
                'sidebar.map',
                'sidebar.alerts',
                'sidebar.sensors',
                'sidebar.cameras',
                'sidebar.access',
                'sidebar.reports',
            ],
        ],
        'viewer' => [
            'permissions' => [
                'map.layers',
                'map.viewports',
                'map.maps',
                'sidebar.dashboard',
                'sidebar.map',
                'sidebar.alerts',
                'sidebar.cameras',
                'sidebar.reports',
            ],
        ],
        'reader' => [
            'permissions' => [
                'map.layers',
                'map.viewports',
                'map.maps',
                'sidebar.dashboard',
                'sidebar.map',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Map Menu Permissions
    |--------------------------------------------------------------------------
    |
    | Maps each menu item to the required permission.
    |
    */

    'map_menu' => [
        'actions' => 'map.actions',
        'edit' => 'map.edit',
        'options' => 'map.options',
        'tools' => 'map.tools',
        'layers' => 'map.layers',
        'viewports' => 'map.viewports',
        'maps' => 'map.maps',
        'import' => 'map.import',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sidebar Menu Permissions
    |--------------------------------------------------------------------------
    |
    | Maps each sidebar menu item to the required permission.
    |
    */

    'sidebar_menu' => [
        'dashboard' => 'sidebar.dashboard',
        'map' => 'sidebar.map',
        'alerts' => 'sidebar.alerts',
        'sensors' => 'sidebar.sensors',
        'cameras' => 'sidebar.cameras',
        'access' => 'sidebar.access',
        'reports' => 'sidebar.reports',
        'devices' => 'sidebar.devices',
        'logs' => 'sidebar.logs',
        'settings' => 'sidebar.settings',
        'settings-general' => null,
        'settings-security' => null,
        'settings-notifications' => null,
        'settings-users' => 'admin.users.manage',
        'settings-roles' => 'admin.roles.manage',
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Menu Permissions
    |--------------------------------------------------------------------------
    |
    | Maps top-bar admin dropdown menu items to required permissions.
    |
    */

    'admin_menu' => [
        'profile' => null, // Available to all authenticated users
        'settings' => null, // Available to all authenticated users
        'users' => 'admin.users.manage',
        'roles' => 'admin.roles.manage',
        'logout' => null, // Available to all authenticated users
    ],
];
