<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import IcasLayout from '@/Layouts/IcasLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Message from 'primevue/message';
import Divider from 'primevue/divider';

const props = defineProps({
    roles: {
        type: Array,
        required: true
    },
    availablePermissions: {
        type: Array,
        required: true
    }
});

// Group permissions by category
const permissionGroups = computed(() => {
    const groups = {
        map: [],
        sidebar: [],
        admin: [],
        other: []
    };

    props.availablePermissions.forEach(permission => {
        if (permission.startsWith('map.')) {
            groups.map.push(permission);
        } else if (permission.startsWith('sidebar.')) {
            groups.sidebar.push(permission);
        } else if (permission.startsWith('admin.')) {
            groups.admin.push(permission);
        } else {
            groups.other.push(permission);
        }
    });

    return groups;
});

// Format role name for display
const formatRoleName = (roleKey) => {
    return roleKey.charAt(0).toUpperCase() + roleKey.slice(1);
};

// Format permission for display
const formatPermission = (permission) => {
    // Remove prefix and replace dots with spaces, then capitalize
    return permission
        .replace(/^(map|sidebar|admin)\./, '')
        .split('.')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

// Check if role has permission
const roleHasPermission = (role, permission) => {
    return role.permissions.includes('*') || role.permissions.includes(permission);
};

// Get role badge color
const getRoleBadgeClass = (roleKey) => {
    const colors = {
        administrator: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        operator: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        viewer: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        reader: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
    };
    return colors[roleKey] || colors.reader;
};
</script>

<template>
    <IcasLayout title="Role Management">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-surface-900 dark:text-surface-0">Role Management</h1>
                    <p class="text-surface-600 dark:text-surface-400 mt-1">View role permissions and access controls</p>
                </div>
            </div>

            <!-- Info Message -->
            <Message severity="info" :closable="false">
                Role permissions are configured in <code class="text-sm bg-surface-100 dark:bg-surface-800 px-1.5 py-0.5 rounded">config/permissions.php</code>. 
                The Administrator role has wildcard (*) access to all features.
            </Message>

            <!-- Roles Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <Card v-for="role in roles" :key="role.key" class="shadow-lg">
                    <template #title>
                        <div class="flex items-center justify-between">
                            <span>{{ formatRoleName(role.key) }}</span>
                            <span 
                                class="text-sm px-3 py-1 rounded-full font-medium"
                                :class="getRoleBadgeClass(role.key)"
                            >
                                {{ role.key }}
                            </span>
                        </div>
                    </template>

                    <template #content>
                        <div class="space-y-4">
                            <!-- Administrator wildcard notice -->
                            <div v-if="roleHasPermission(role, '*')" class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                <div class="flex items-start gap-3">
                                    <i class="pi pi-shield text-red-600 dark:text-red-400 text-xl mt-0.5"></i>
                                    <div>
                                        <p class="font-semibold text-red-900 dark:text-red-200">Full Administrator Access</p>
                                        <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                                            This role has unrestricted access to all features and permissions via wildcard (*).
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Map Permissions -->
                            <div v-if="permissionGroups.map.length > 0">
                                <h4 class="font-semibold text-surface-900 dark:text-surface-0 mb-2 flex items-center gap-2">
                                    <i class="pi pi-map text-blue-600"></i>
                                    Map Permissions
                                </h4>
                                <div class="space-y-2 pl-6">
                                    <div 
                                        v-for="permission in permissionGroups.map" 
                                        :key="permission"
                                        class="flex items-center gap-2"
                                    >
                                        <i 
                                            :class="roleHasPermission(role, permission) ? 'pi pi-check-circle text-green-600' : 'pi pi-times-circle text-gray-400'"
                                        ></i>
                                        <span 
                                            class="text-sm"
                                            :class="roleHasPermission(role, permission) ? 'text-surface-900 dark:text-surface-0' : 'text-surface-500'"
                                        >
                                            {{ formatPermission(permission) }}
                                        </span>
                                    </div>
                                </div>
                                <Divider />
                            </div>

                            <!-- Sidebar Permissions -->
                            <div v-if="permissionGroups.sidebar.length > 0">
                                <h4 class="font-semibold text-surface-900 dark:text-surface-0 mb-2 flex items-center gap-2">
                                    <i class="pi pi-bars text-purple-600"></i>
                                    Sidebar Permissions
                                </h4>
                                <div class="space-y-2 pl-6">
                                    <div 
                                        v-for="permission in permissionGroups.sidebar" 
                                        :key="permission"
                                        class="flex items-center gap-2"
                                    >
                                        <i 
                                            :class="roleHasPermission(role, permission) ? 'pi pi-check-circle text-green-600' : 'pi pi-times-circle text-gray-400'"
                                        ></i>
                                        <span 
                                            class="text-sm"
                                            :class="roleHasPermission(role, permission) ? 'text-surface-900 dark:text-surface-0' : 'text-surface-500'"
                                        >
                                            {{ formatPermission(permission) }}
                                        </span>
                                    </div>
                                </div>
                                <Divider />
                            </div>

                            <!-- Admin Permissions -->
                            <div v-if="permissionGroups.admin.length > 0">
                                <h4 class="font-semibold text-surface-900 dark:text-surface-0 mb-2 flex items-center gap-2">
                                    <i class="pi pi-shield text-red-600"></i>
                                    Admin Permissions
                                </h4>
                                <div class="space-y-2 pl-6">
                                    <div 
                                        v-for="permission in permissionGroups.admin" 
                                        :key="permission"
                                        class="flex items-center gap-2"
                                    >
                                        <i 
                                            :class="roleHasPermission(role, permission) ? 'pi pi-check-circle text-green-600' : 'pi pi-times-circle text-gray-400'"
                                        ></i>
                                        <span 
                                            class="text-sm"
                                            :class="roleHasPermission(role, permission) ? 'text-surface-900 dark:text-surface-0' : 'text-surface-500'"
                                        >
                                            {{ formatPermission(permission) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Permission Count Summary -->
                            <div class="mt-4 p-3 bg-surface-50 dark:bg-surface-800 rounded-lg">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-surface-600 dark:text-surface-400">Total Permissions:</span>
                                    <span class="font-semibold text-surface-900 dark:text-surface-0">
                                        {{ roleHasPermission(role, '*') ? 'All (*)' : role.permissions.length }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Permission Legend -->
            <Card class="shadow-lg bg-blue-50 dark:bg-blue-900/20">
                <template #title>
                    <div class="flex items-center gap-2 text-blue-900 dark:text-blue-200">
                        <i class="pi pi-info-circle"></i>
                        <span>Permission System Overview</span>
                    </div>
                </template>
                <template #content>
                    <div class="space-y-3 text-sm text-surface-700 dark:text-surface-300">
                        <p>
                            <strong>Map Permissions:</strong> Control access to map-related features such as DXF import, layer management, and map controls.
                        </p>
                        <p>
                            <strong>Sidebar Permissions:</strong> Determine which navigation menu items are visible in the left sidebar.
                        </p>
                        <p>
                            <strong>Admin Permissions:</strong> Grant access to administrative functions like user management and role configuration.
                        </p>
                        <Divider />
                        <p class="text-xs text-surface-600 dark:text-surface-400">
                            <i class="pi pi-exclamation-triangle mr-1"></i>
                            To modify role permissions, edit the <code class="bg-surface-200 dark:bg-surface-700 px-1.5 py-0.5 rounded">config/permissions.php</code> file and clear the cache.
                        </p>
                    </div>
                </template>
            </Card>
        </div>
    </IcasLayout>
</template>
