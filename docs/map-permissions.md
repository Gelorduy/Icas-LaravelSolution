# Map Permissions System

## Overview

The ICAS map workspace uses a role-based permission system to control which menu items and features users can access. This ensures that users only see the tools relevant to their role.

## Roles

### Administrator
- **Full Access**: Has wildcard permission (`*`) granting access to all features
- Sees all 7 menu items: Actions, Edit, Options, Tools, Layers, ViewPorts, Import DXF

### Operator
- **Permissions**: `map.actions`, `map.layers`, `map.viewports`, `map.options`
- Can perform operational tasks, view layers, navigate viewports, and adjust map options
- Sees 4 menu items: Actions, Options, Layers, ViewPorts

### Viewer / Reader
- **Permissions**: `map.layers`, `map.viewports`
- Can only view layers and navigate viewports
- Sees 2 menu items: Layers, ViewPorts

## Configuration

Permissions are defined in [config/permissions.php](../config/permissions.php):

```php
'roles' => [
    'administrator' => ['permissions' => ['*']],
    'operator' => ['permissions' => ['map.actions', 'map.layers', 'map.viewports', 'map.options']],
    'viewer' => ['permissions' => ['map.layers', 'map.viewports']],
    'reader' => ['permissions' => ['map.layers', 'map.viewports']],
],

'map_menu' => [
    'actions' => 'map.actions',
    'edit' => 'map.edit',
    'options' => 'map.options',
    'tools' => 'map.tools',
    'layers' => 'map.layers',
    'viewports' => 'map.viewports',
    'import' => 'map.import',
]
```

## Architecture

### Backend

**PermissionService** (`app/Services/PermissionService.php`):
- `userHasPermission(User $user, string $permission)` - Check if user has specific permission
- `getUserPermissions(User $user)` - Get all permissions for a user
- `getAllowedMapMenuItems(User $user)` - Get menu items user can access

**HandleInertiaRequests Middleware** (`app/Http/Middleware/HandleInertiaRequests.php`):
- Shares permissions data with frontend via `$page.props.permissions`
- Includes `allowedMapMenuItems` array and `userPermissions` array

### Frontend

**MapMenu Component** (`resources/js/Components/Map/MapMenu.vue`):
- Filters menu items based on `$page.props.permissions.allowedMapMenuItems`
- Only renders buttons for allowed items

**MapWorkspace Component** (`resources/js/Components/Map/MapWorkspace.vue`):
- Validates menu selections against allowed items
- Automatically adjusts active menu key when permissions change
- Handles default menu selection based on user permissions

## Adding New Roles

1. Add role to `config/permissions.php`:
```php
'roles' => [
    'new_role' => [
        'permissions' => ['map.layers', 'map.viewports', 'map.actions']
    ],
]
```

2. Update database if role doesn't exist in users table

## Adding New Menu Items

1. Add permission to `config/permissions.php`:
```php
'map_menu' => [
    'new_feature' => 'map.new_feature',
]
```

2. Grant permission to appropriate roles:
```php
'operator' => [
    'permissions' => ['map.actions', 'map.layers', ..., 'map.new_feature']
]
```

3. Add menu item to `MapMenu.vue`:
```javascript
const allMenuTabs = [
    { key: 'new_feature', label: 'New Feature' },
    // ...
];
```

4. Add overlay metadata to `MapWorkspace.vue`:
```javascript
const overlayMetadata = {
    new_feature: {
        title: 'New Feature',
        description: 'Description of what this feature does.'
    },
    // ...
};
```

## Testing

Current admin user has all permissions:
- Email: `admin@icas.local`
- Password: `inmate.2025`
- Role: `administrator`

To test other roles, create test users with different roles in the database.

## Security Notes

- All permission checks happen server-side via PermissionService
- Frontend filtering is for UX only - backend must enforce permissions
- Administrator role bypasses all permission checks via wildcard (`*`)
- Missing roles default to 'reader' permissions
