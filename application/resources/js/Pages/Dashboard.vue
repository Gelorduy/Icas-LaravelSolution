<script setup>
import { ref } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import StyleClass from 'primevue/styleclass';
import Button from 'primevue/button';
import Avatar from 'primevue/avatar';
import Badge from 'primevue/badge';
import Dropdown from 'primevue/dropdown';
import Menu from 'primevue/menu';

const vStyleclass = StyleClass;
const activeMenuItem = ref('dashboard');
const sidebarExpanded = ref(false);
const userMenuRef = ref();
const $page = usePage();

// Sample site data
const selectedSite = ref({ name: 'Monroe County Jail', code: 'MCJ' });
const sites = ref([
  { name: 'Monroe County Jail', code: 'MCJ' },
  { name: 'City Center Facility', code: 'CCF' },
  { name: 'North Campus', code: 'NC' },
]);

const logout = () => {
  router.post(route('logout'));
};

const toggleUserMenu = (event) => {
  userMenuRef.value.toggle(event);
};

const userMenuItems = ref([
  {
    label: 'Profile',
    icon: 'pi pi-user',
    command: () => router.visit(route('profile.show'))
  },
  {
    separator: true
  },
  {
    label: 'Logout',
    icon: 'pi pi-sign-out',
    command: logout
  }
]);

// All menu items with icons and optional submenus
const menuItems = ref([
  {
    key: 'dashboard',
    label: 'Dashboard',
    icon: 'pi pi-home',
    command: () => activeMenuItem.value = 'dashboard'
  },
  {
    key: 'map',
    label: 'Map Visualization',
    icon: 'pi pi-map',
    command: () => activeMenuItem.value = 'map'
  },
  {
    key: 'alerts',
    label: 'Alerts',
    icon: 'pi pi-bell',
    items: [
      { key: 'alerts-active', label: 'Active Alerts', icon: 'pi pi-exclamation-circle', command: () => activeMenuItem.value = 'alerts-active' },
      { key: 'alerts-history', label: 'Alert History', icon: 'pi pi-history', command: () => activeMenuItem.value = 'alerts-history' },
      { key: 'alerts-config', label: 'Alert Configuration', icon: 'pi pi-cog', command: () => activeMenuItem.value = 'alerts-config' },
    ],
  },
  {
    key: 'sensors',
    label: 'Sensors',
    icon: 'pi pi-chart-line',
    items: [
      { key: 'sensors-status', label: 'Sensor Status', icon: 'pi pi-info-circle', command: () => activeMenuItem.value = 'sensors-status' },
      { key: 'sensors-manage', label: 'Manage Sensors', icon: 'pi pi-sliders-h', command: () => activeMenuItem.value = 'sensors-manage' },
    ],
  },
  {
    key: 'cameras',
    label: 'Cameras',
    icon: 'pi pi-video',
    items: [
      { key: 'cameras-live', label: 'Live View', icon: 'pi pi-eye', command: () => activeMenuItem.value = 'cameras-live' },
      { key: 'cameras-recordings', label: 'Recordings', icon: 'pi pi-circle-fill', command: () => activeMenuItem.value = 'cameras-recordings' },
    ],
  },
  {
    key: 'access',
    label: 'Access Control',
    icon: 'pi pi-lock',
    command: () => activeMenuItem.value = 'access'
  },
  {
    key: 'reports',
    label: 'Reports',
    icon: 'pi pi-chart-bar',
    items: [
      { key: 'reports-daily', label: 'Daily Report', icon: 'pi pi-calendar', command: () => activeMenuItem.value = 'reports-daily' },
      { key: 'reports-monthly', label: 'Monthly Report', icon: 'pi pi-calendar', command: () => activeMenuItem.value = 'reports-monthly' },
      { key: 'reports-custom', label: 'Custom Report', icon: 'pi pi-sliders-h', command: () => activeMenuItem.value = 'reports-custom' },
    ],
  },
  {
    key: 'users',
    label: 'User Management',
    icon: 'pi pi-users',
    command: () => activeMenuItem.value = 'users'
  },
  {
    key: 'devices',
    label: 'Devices',
    icon: 'pi pi-tablet',
    command: () => activeMenuItem.value = 'devices'
  },
  {
    key: 'logs',
    label: 'System Logs',
    icon: 'pi pi-book',
    command: () => activeMenuItem.value = 'logs'
  },
  {
    key: 'settings',
    label: 'Settings',
    icon: 'pi pi-cog',
    items: [
      { key: 'settings-general', label: 'General Settings', icon: 'pi pi-cog', command: () => activeMenuItem.value = 'settings-general' },
      { key: 'settings-security', label: 'Security', icon: 'pi pi-shield', command: () => activeMenuItem.value = 'settings-security' },
      { key: 'settings-notifications', label: 'Notifications', icon: 'pi pi-bell', command: () => activeMenuItem.value = 'settings-notifications' },
    ],
  },
]);
</script>

<template>
  <div>
    <Head title="Dashboard" />
    
    <div class="resize-container-10 min-h-screen flex relative lg:static bg-surface-0 dark:bg-surface-950">
      <!-- Sidebar (Narrow by default) -->
      <div id="app-sidebar-narrow" class="bg-surface-900 h-screen hidden lg:flex shrink-0 absolute lg:static left-0 top-0 z-10 transition-all duration-300" :class="sidebarExpanded ? 'w-72' : 'w-20'">
        <div class="flex flex-col h-full w-full">
          <!-- Logo Section -->
          <div class="flex items-center justify-between shrink-0 bg-primary p-4 border border-primary">
            <div v-if="sidebarExpanded" class="flex items-center gap-2 text-primary-contrast">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 33 32" fill="none">
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M7.34219 2.87829C6.19766 3.67858 5.1627 4.62478 4.26426 5.68992C7.9357 5.34906 12.6001 5.90564 18.0155 8.61335C23.7984 11.5047 28.455 11.6025 31.6958 10.9773C31.4017 10.087 31.0315 9.23135 30.593 8.41791C26.8832 8.80919 22.1272 8.29127 16.5845 5.51998C13.0648 3.76014 9.96221 3.03521 7.34219 2.87829ZM28.4259 5.33332C25.4962 2.06 21.2387 0 16.5 0C15.1084 0 13.7581 0.177686 12.4709 0.511584C14.2143 0.987269 16.0663 1.68319 18.0155 2.65781C22.0736 4.68682 25.5771 5.34013 28.4259 5.33332ZM32.3887 14.1025C28.4735 14.8756 23.067 14.7168 16.5845 11.4755C10.524 8.44527 5.70035 8.48343 2.44712 9.20639C2.2792 9.24367 2.11523 9.28287 1.95522 9.32367C1.5293 10.25 1.18931 11.2241 0.945362 12.2356C1.20591 12.166 1.47514 12.0998 1.75293 12.0381C5.69966 11.161 11.2761 11.1991 18.0155 14.5689C24.0761 17.5991 28.8997 17.561 32.1529 16.838C32.2644 16.8133 32.3742 16.7877 32.4822 16.7613C32.4941 16.509 32.5 16.2552 32.5 16C32.5 15.358 32.4622 14.7248 32.3887 14.1025ZM31.9598 20.1378C28.0826 20.8157 22.8336 20.5555 16.5845 17.431C10.524 14.4008 5.70035 14.439 2.44712 15.1619C1.725 15.3223 1.07539 15.5178 0.502344 15.7241C0.500782 15.8158 0.5 15.9078 0.5 16C0.5 24.8366 7.66344 32 16.5 32C23.9057 32 30.1362 26.9687 31.9598 20.1378Z"
                  class="fill-primary-contrast"
                />
              </svg>
              <span class="font-bold text-lg text-primary-contrast">ICAS</span>
            </div>
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 33 32" fill="none">
              <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M7.34219 2.87829C6.19766 3.67858 5.1627 4.62478 4.26426 5.68992C7.9357 5.34906 12.6001 5.90564 18.0155 8.61335C23.7984 11.5047 28.455 11.6025 31.6958 10.9773C31.4017 10.087 31.0315 9.23135 30.593 8.41791C26.8832 8.80919 22.1272 8.29127 16.5845 5.51998C13.0648 3.76014 9.96221 3.03521 7.34219 2.87829ZM28.4259 5.33332C25.4962 2.06 21.2387 0 16.5 0C15.1084 0 13.7581 0.177686 12.4709 0.511584C14.2143 0.987269 16.0663 1.68319 18.0155 2.65781C22.0736 4.68682 25.5771 5.34013 28.4259 5.33332ZM32.3887 14.1025C28.4735 14.8756 23.067 14.7168 16.5845 11.4755C10.524 8.44527 5.70035 8.48343 2.44712 9.20639C2.2792 9.24367 2.11523 9.28287 1.95522 9.32367C1.5293 10.25 1.18931 11.2241 0.945362 12.2356C1.20591 12.166 1.47514 12.0998 1.75293 12.0381C5.69966 11.161 11.2761 11.1991 18.0155 14.5689C24.0761 17.5991 28.8997 17.561 32.1529 16.838C32.2644 16.8133 32.3742 16.7877 32.4822 16.7613C32.4941 16.509 32.5 16.2552 32.5 16C32.5 15.358 32.4622 14.7248 32.3887 14.1025ZM31.9598 20.1378C28.0826 20.8157 22.8336 20.5555 16.5845 17.431C10.524 14.4008 5.70035 14.439 2.44712 15.1619C1.725 15.3223 1.07539 15.5178 0.502344 15.7241C0.500782 15.8158 0.5 15.9078 0.5 16C0.5 24.8366 7.66344 32 16.5 32C23.9057 32 30.1362 26.9687 31.9598 20.1378Z"
                class="fill-primary-contrast"
              />
            </svg>

            <!-- Expand/Collapse Button -->
            <button
              class="text-primary-contrast hover:opacity-80 transition-opacity"
              @click="sidebarExpanded = !sidebarExpanded"
            >
              <i :class="sidebarExpanded ? 'pi pi-chevron-left' : 'pi pi-chevron-right'" />
            </button>
          </div>

          <!-- Menu Items Container -->
          <div class="flex-1 p-2 flex flex-col items-center overflow-y-auto lg:overflow-visible border-r border-surface-800" :class="sidebarExpanded ? 'items-start gap-1' : 'gap-2'">
            <!-- Loop through all menu items -->
            <div v-for="item in menuItems" :key="item.key" class="w-full lg:relative">
              <!-- Item without submenu -->
              <a
                v-if="!item.items"
                class="w-full flex items-center cursor-pointer p-3 rounded-md text-surface-400 border border-transparent hover:bg-surface-800 hover:border-surface-700 hover:text-surface-0 transition-colors duration-150 group"
                :class="sidebarExpanded ? 'justify-start gap-3' : 'justify-center'"
                :title="item.label"
                @click="item.command()"
              >
                <i :class="[item.icon, 'text-lg! leading-none! text-surface-400 group-hover:text-surface-0 shrink-0']" />
                <span v-if="sidebarExpanded" class="font-medium text-base leading-tight text-surface-400 group-hover:text-surface-0">{{ item.label }}</span>
              </a>

              <!-- Item with submenu -->
              <div v-else class="lg:relative w-full">
                <a
                  v-styleclass="{ selector: '@next', enterFromClass: 'hidden', leaveToClass: 'hidden', hideOnOutsideClick: true }"
                  class="w-full flex items-center cursor-pointer p-3 lg:justify-center rounded-md text-surface-400 border border-transparent hover:bg-surface-800 hover:border-surface-700 hover:text-surface-0 transition-colors duration-150 group"
                  :class="sidebarExpanded ? 'justify-between' : 'justify-center'"
                  :title="item.label"
                >
                  <i :class="[item.icon, 'text-lg! leading-none! text-surface-400 group-hover:text-surface-0 shrink-0 mr-2 lg:mr-0']" />
                  <span class="font-medium text-base leading-tight inline lg:hidden">{{ item.label }}</span>
                  <i class="pi pi-chevron-down ml-auto lg:hidden! flex!" />
                </a>

                <!-- Submenu List -->
                <ul
                  class="list-none pl-4 pr-0 py-0 lg:p-2 m-0 lg:ml-4 hidden overflow-y-hidden transition-all duration-400 ease-in-out static lg:absolute left-full top-0 z-20 bg-white border border-surface-200 rounded-r shadow-lg lg:shadow-xl lg:w-60 rounded-lg"
                >
                  <li v-for="subitem in item.items" :key="subitem.key">
                    <a
                      class="flex items-center cursor-pointer p-3 gap-2 rounded-lg text-surface-400 hover:bg-surface-800 hover:text-surface-0 border border-transparent hover:border-surface-700 transition-colors duration-150 group"
                      @click="subitem.command()"
                    >
                      <i :class="[subitem.icon, 'text-base! leading-none! text-surface-400 group-hover:text-surface-0']" />
                      <span class="font-medium text-base leading-tight">{{ subitem.label }}</span>
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <!-- User Section at Bottom -->
          <div class="mt-auto flex flex-col items-center pt-4 pb-6 px-4 gap-4" :class="sidebarExpanded ? 'items-start w-full' : ''">
            <hr class="w-full border-t border-surface-800" />
            <a class="flex items-center cursor-pointer p-0 text-surface-400 hover:text-surface-0 transition-colors duration-150" :class="sidebarExpanded ? 'gap-2' : 'justify-center'">
              <img src="https://fqjltiegiezfetthbags.supabase.co/storage/v1/render/image/public/block.images/blocks/avatars/avatar-amyels.png" class="w-8 h-8 rounded-full cursor-pointer shrink-0" />
              <span v-if="sidebarExpanded" class="font-medium text-base leading-tight text-surface-400 hover:text-surface-0">{{ $page.props.auth.user.name }}</span>
            </a>
          </div>
        </div>
      </div>

      <!-- Main Content Area -->
      <div class="min-h-screen flex flex-col relative flex-auto">
        <!-- Top Navigation Bar -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white shadow-lg sticky top-0 z-50">
          <div class="flex items-center justify-between px-6 py-3">
            <!-- Left: Logo, Mobile Menu, and Site Selector -->
            <div class="flex items-center gap-6">
              <!-- Mobile Menu Toggle -->
              <a
                v-styleclass="{
                  selector: '#app-sidebar-narrow',
                  enterFromClass: 'hidden',
                  enterActiveClass: 'animate-fadeinleft',
                  leaveToClass: 'hidden',
                  leaveActiveClass: 'animate-fadeoutleft',
                  hideOnOutsideClick: true,
                  resizeSelector: '.resize-container-10',
                  hideOnResize: true
                }"
                class="cursor-pointer flex items-center justify-center lg:hidden text-white"
              >
                <i class="pi pi-bars text-xl" />
              </a>

              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                  <span class="text-blue-700 font-bold text-lg">IC</span>
                </div>
                <span class="text-2xl font-bold">ICAS</span>
              </div>

              <!-- Site Dropdown -->
              <div class="flex items-center gap-2">
                <span class="text-sm font-semibold">Site:</span>
                <Dropdown 
                  v-model="selectedSite" 
                  :options="sites" 
                  optionLabel="name"
                  placeholder="Select a Site"
                  class="w-64"
                />
              </div>
            </div>

            <!-- Right: Action Icons and User Menu -->
            <div class="flex items-center gap-4">
              <!-- Queue Icon -->
              <Button 
                icon="pi pi-list" 
                rounded 
                text 
                severity="secondary" 
                aria-label="Queue"
                class="text-white hover:bg-blue-700"
              />

              <!-- Help Icon -->
              <Button 
                icon="pi pi-question-circle" 
                rounded 
                text 
                severity="secondary" 
                aria-label="Help"
                class="text-white hover:bg-blue-700"
              />

              <!-- Notifications Icon with Badge -->
              <Button 
                icon="pi pi-bell" 
                rounded 
                text 
                severity="secondary" 
                aria-label="Notifications"
                class="text-white hover:bg-blue-700 relative"
              >
                <Badge value="3" severity="danger" class="absolute -top-1 -right-1" />
              </Button>

              <!-- User Menu -->
              <div class="flex items-center gap-2 cursor-pointer hover:bg-blue-700 px-3 py-2 rounded-lg" @click="toggleUserMenu">
                <Avatar 
                  :label="$page.props.auth.user.name.charAt(0).toUpperCase()" 
                  class="bg-yellow-400 text-blue-800"
                  shape="circle" 
                />
                <span class="text-sm font-medium">{{ $page.props.auth.user.name }}</span>
                <i class="pi pi-chevron-down text-xs"></i>
              </div>
              <Menu ref="userMenuRef" :model="userMenuItems" popup />
            </div>
          </div>
        </div>

        <!-- Content Area -->
        <div class="p-8 flex flex-col flex-auto overflow-auto">
          <!-- Dashboard Content -->
          <div 
            v-if="activeMenuItem === 'dashboard'"
            class="space-y-6"
          >
            <h1 class="text-3xl font-bold text-surface-900 dark:text-surface-0">Dashboard</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <div class="bg-surface-50 dark:bg-surface-800 p-6 rounded-lg border border-surface-200 dark:border-surface-700">
                <p class="text-surface-600 dark:text-surface-300 text-sm font-medium">Active Sensors</p>
                <p class="text-3xl font-bold text-surface-900 dark:text-surface-0 mt-2">24</p>
              </div>
              <div class="bg-surface-50 dark:bg-surface-800 p-6 rounded-lg border border-surface-200 dark:border-surface-700">
                <p class="text-surface-600 dark:text-surface-300 text-sm font-medium">Active Alerts</p>
                <p class="text-3xl font-bold text-surface-900 dark:text-surface-0 mt-2">3</p>
              </div>
              <div class="bg-surface-50 dark:bg-surface-800 p-6 rounded-lg border border-surface-200 dark:border-surface-700">
                <p class="text-surface-600 dark:text-surface-300 text-sm font-medium">System Status</p>
                <p class="text-3xl font-bold text-green-600 mt-2">Online</p>
              </div>
              <div class="bg-surface-50 dark:bg-surface-800 p-6 rounded-lg border border-surface-200 dark:border-surface-700">
                <p class="text-surface-600 dark:text-surface-300 text-sm font-medium">Connected Devices</p>
                <p class="text-3xl font-bold text-surface-900 dark:text-surface-0 mt-2">32</p>
              </div>
            </div>
          </div>

          <!-- Map View -->
          <div 
            v-else-if="activeMenuItem === 'map'"
            class="space-y-6"
          >
            <h1 class="text-3xl font-bold text-surface-900 dark:text-surface-0">Map Visualization</h1>
            <div class="border-2 border-dashed border-surface-200 dark:border-surface-700 rounded-2xl bg-surface-50 dark:bg-surface-800 p-12 flex items-center justify-center min-h-96">
              <div class="text-center">
                <i class="pi pi-map text-6xl text-surface-400 mb-4"></i>
                <p class="text-surface-500 text-lg font-semibold">Map Canvas Area</p>
              </div>
            </div>
          </div>

          <!-- Generic Content -->
          <div 
            v-else
            class="space-y-6"
          >
            <h1 class="text-3xl font-bold text-surface-900 dark:text-surface-0 capitalize">{{ activeMenuItem.replace(/-/g, ' ') }}</h1>
            <div class="border-2 border-dashed border-surface-200 dark:border-surface-700 rounded-2xl bg-surface-50 dark:bg-surface-800 p-8 flex-auto flex items-center justify-center min-h-96">
              <p class="text-surface-600 dark:text-surface-300 text-lg">Content for {{ activeMenuItem }} will be implemented here</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
:deep(.p-styleclass-enter) {
  animation: slideInLeft 300ms ease-in;
}

:deep(.p-styleclass-leave) {
  animation: slideOutLeft 300ms ease-out;
}

@keyframes slideInLeft {
  from {
    transform: translateX(-100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@keyframes slideOutLeft {
  from {
    transform: translateX(0);
    opacity: 1;
  }
  to {
    transform: translateX(-100%);
    opacity: 0;
  }
}

/* Allow sidebar width transition */
#app-sidebar-narrow {
  will-change: width;
}

/* Custom dropdown styling for blue theme */
.p-dropdown {
  background: rgba(29, 78, 216, 0.5) !important;
  border-color: rgba(255, 255, 255, 0.3) !important;
  color: white !important;
}

.p-dropdown:hover {
  background: rgba(29, 78, 216, 0.7) !important;
}

.p-dropdown-label {
  color: white !important;
}

/* Dropdown menu items styling */
.p-dropdown-items {
  background: white !important;
}

.p-dropdown-item {
  color: #333 !important;
}

.p-dropdown-item:hover {
  background: #f3f4f6 !important;
}
</style>
