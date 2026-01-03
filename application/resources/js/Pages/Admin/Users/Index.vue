<script setup>
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import IcasLayout from '@/Layouts/IcasLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Password from 'primevue/password';
import Message from 'primevue/message';

const props = defineProps({
    users: {
        type: Array,
        required: true
    },
    availableRoles: {
        type: Array,
        required: true
    }
});

// Dialog states
const showUserDialog = ref(false);
const showDeleteDialog = ref(false);
const dialogMode = ref('create'); // 'create' or 'edit'
const selectedUser = ref(null);

// Form
const form = useForm({
    id: null,
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'reader'
});

// Open create dialog
const openCreateDialog = () => {
    dialogMode.value = 'create';
    form.reset();
    form.clearErrors();
    form.role = 'reader';
    showUserDialog.value = true;
};

// Open edit dialog
const openEditDialog = (user) => {
    dialogMode.value = 'edit';
    selectedUser.value = user;
    form.clearErrors();
    form.id = user.id;
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.password_confirmation = '';
    form.role = user.role;
    showUserDialog.value = true;
};

// Open delete dialog
const openDeleteDialog = (user) => {
    selectedUser.value = user;
    showDeleteDialog.value = true;
};

// Submit user form
const submitUserForm = () => {
    if (dialogMode.value === 'create') {
        form.post(route('admin.users.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showUserDialog.value = false;
                form.reset();
            }
        });
    } else {
        form.put(route('admin.users.update', form.id), {
            preserveScroll: true,
            onSuccess: () => {
                showUserDialog.value = false;
                form.reset();
            }
        });
    }
};

// Delete user
const deleteUser = () => {
    router.delete(route('admin.users.destroy', selectedUser.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            selectedUser.value = null;
        }
    });
};

// Role display formatting
const roleOptions = computed(() => {
    return props.availableRoles.map(role => ({
        label: role.charAt(0).toUpperCase() + role.slice(1),
        value: role
    }));
});

const formatRole = (role) => {
    return role.charAt(0).toUpperCase() + role.slice(1);
};
</script>

<template>
    <IcasLayout title="User Management">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-surface-900 dark:text-surface-0">User Management</h1>
                    <p class="text-surface-600 dark:text-surface-400 mt-1">Create and manage user accounts and their roles</p>
                </div>
                <Button label="Create User" icon="pi pi-plus" @click="openCreateDialog" />
            </div>

            <!-- Users DataTable -->
            <div class="bg-surface-0 dark:bg-surface-900 rounded-lg shadow">
                <DataTable 
                    :value="users" 
                    :paginator="true" 
                    :rows="10"
                    :rowsPerPageOptions="[5, 10, 25, 50]"
                    stripedRows
                    filterDisplay="row"
                    :globalFilterFields="['name', 'email', 'role']"
                >
                    <Column field="name" header="Name" sortable>
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <i class="pi pi-user text-surface-500"></i>
                                <span class="font-medium">{{ data.name }}</span>
                            </div>
                        </template>
                    </Column>
                    <Column field="email" header="Email" sortable></Column>
                    <Column field="role" header="Role" sortable>
                        <template #body="{ data }">
                            <span 
                                class="px-3 py-1 rounded-full text-sm font-medium"
                                :class="{
                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': data.role === 'administrator',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': data.role === 'operator',
                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': data.role === 'viewer',
                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200': data.role === 'reader'
                                }"
                            >
                                {{ formatRole(data.role) }}
                            </span>
                        </template>
                    </Column>
                    <Column field="created_at" header="Created" sortable>
                        <template #body="{ data }">
                            <span class="text-surface-600 dark:text-surface-400">{{ data.created_at }}</span>
                        </template>
                    </Column>
                    <Column header="Actions">
                        <template #body="{ data }">
                            <div class="flex gap-2">
                                <Button 
                                    icon="pi pi-pencil" 
                                    severity="secondary" 
                                    size="small" 
                                    text
                                    @click="openEditDialog(data)"
                                    v-tooltip.top="'Edit User'"
                                />
                                <Button 
                                    icon="pi pi-trash" 
                                    severity="danger" 
                                    size="small" 
                                    text
                                    @click="openDeleteDialog(data)"
                                    v-tooltip.top="'Delete User'"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>

            <!-- User Create/Edit Dialog -->
            <Dialog 
                v-model:visible="showUserDialog" 
                :header="dialogMode === 'create' ? 'Create User' : 'Edit User'"
                :modal="true"
                :style="{ width: '32rem' }"
                :dismissableMask="true"
            >
                <form @submit.prevent="submitUserForm" class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium mb-2">Name</label>
                        <InputText 
                            id="name" 
                            v-model="form.name" 
                            class="w-full"
                            :class="{ 'p-invalid': form.errors.name }"
                        />
                        <Message v-if="form.errors.name" severity="error" :closable="false" class="mt-2">
                            {{ form.errors.name }}
                        </Message>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium mb-2">Email</label>
                        <InputText 
                            id="email" 
                            v-model="form.email" 
                            type="email"
                            class="w-full"
                            :class="{ 'p-invalid': form.errors.email }"
                        />
                        <Message v-if="form.errors.email" severity="error" :closable="false" class="mt-2">
                            {{ form.errors.email }}
                        </Message>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium mb-2">
                            Password 
                            <span v-if="dialogMode === 'edit'" class="text-surface-500 font-normal">(leave blank to keep current)</span>
                        </label>
                        <Password 
                            id="password" 
                            v-model="form.password" 
                            toggleMask
                            :feedback="dialogMode === 'create'"
                            class="w-full"
                            inputClass="w-full"
                            :class="{ 'p-invalid': form.errors.password }"
                        />
                        <Message v-if="form.errors.password" severity="error" :closable="false" class="mt-2">
                            {{ form.errors.password }}
                        </Message>
                    </div>

                    <!-- Password Confirmation -->
                    <div v-if="form.password">
                        <label for="password_confirmation" class="block text-sm font-medium mb-2">Confirm Password</label>
                        <Password 
                            id="password_confirmation" 
                            v-model="form.password_confirmation" 
                            :feedback="false"
                            toggleMask
                            class="w-full"
                            inputClass="w-full"
                        />
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-sm font-medium mb-2">Role</label>
                        <Dropdown 
                            id="role" 
                            v-model="form.role" 
                            :options="roleOptions"
                            optionLabel="label"
                            optionValue="value"
                            class="w-full"
                            :class="{ 'p-invalid': form.errors.role }"
                        />
                        <Message v-if="form.errors.role" severity="error" :closable="false" class="mt-2">
                            {{ form.errors.role }}
                        </Message>
                    </div>

                    <!-- Dialog Footer -->
                    <div class="flex justify-end gap-2 pt-4">
                        <Button 
                            label="Cancel" 
                            severity="secondary" 
                            @click="showUserDialog = false"
                            type="button"
                        />
                        <Button 
                            :label="dialogMode === 'create' ? 'Create' : 'Update'" 
                            type="submit"
                            :loading="form.processing"
                        />
                    </div>
                </form>
            </Dialog>

            <!-- Delete Confirmation Dialog -->
            <Dialog 
                v-model:visible="showDeleteDialog" 
                header="Confirm Deletion"
                :modal="true"
                :style="{ width: '28rem' }"
            >
                <div class="space-y-4">
                    <p class="text-surface-700 dark:text-surface-300">
                        Are you sure you want to delete <strong>{{ selectedUser?.name }}</strong>? This action cannot be undone.
                    </p>

                    <div class="flex justify-end gap-2">
                        <Button 
                            label="Cancel" 
                            severity="secondary" 
                            @click="showDeleteDialog = false"
                        />
                        <Button 
                            label="Delete" 
                            severity="danger" 
                            @click="deleteUser"
                        />
                    </div>
                </div>
            </Dialog>
        </div>
    </IcasLayout>
</template>
