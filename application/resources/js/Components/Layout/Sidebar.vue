<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import StyleClass from 'primevue/styleclass';

const vStyleclass = StyleClass;
const page = usePage();

const props = defineProps({
  expanded: {
    type: Boolean,
    default: false
  },
  activeMenuItem: {
    type: String,
    default: 'dashboard'
  }
});

const emit = defineEmits(['update:expanded']);

const toggleSidebar = () => {
  emit('update:expanded', !props.expanded);
};

// Track which submenus are open when sidebar is expanded
const openSubmenus = ref([]);

const toggleSubmenu = (itemKey) => {
  const index = openSubmenus.value.indexOf(itemKey);
  if (index > -1) {
    openSubmenus.value.splice(index, 1);
  } else {
    openSubmenus.value.push(itemKey);
  }
};

const isSubmenuOpen = (itemKey) => {
  return openSubmenus.value.includes(itemKey);
};

const allowedSidebarItems = computed(() => page.props.permissions?.allowedSidebarMenuItems || []);

// All menu items with icons and optional submenus
const allMenuItems = ref([
  {
    key: 'dashboard',
    label: 'Dashboard',
    icon: 'pi pi-home',
    route: 'dashboard'
  },
  {
    key: 'map',
    label: 'Map Visualization',
    icon: 'pi pi-map',
    route: 'map.index'
  },
  {
    key: 'alerts',
    label: 'Alerts',
    icon: 'pi pi-bell',
    items: [
      { key: 'alerts-active', label: 'Active Alerts', icon: 'pi pi-exclamation-circle', route: 'alerts.active' },
      { key: 'alerts-history', label: 'Alert History', icon: 'pi pi-history', route: 'alerts.history' },
      { key: 'alerts-config', label: 'Alert Configuration', icon: 'pi pi-cog', route: 'alerts.config' },
    ],
  },
  {
    key: 'sensors',
    label: 'Sensors',
    icon: 'pi pi-chart-line',
    items: [
      { key: 'sensors-status', label: 'Sensor Status', icon: 'pi pi-info-circle', route: 'sensors.status' },
      { key: 'sensors-manage', label: 'Manage Sensors', icon: 'pi pi-sliders-h', route: 'sensors.manage' },
    ],
  },
  {
    key: 'cameras',
    label: 'Cameras',
    icon: 'pi pi-video',
    items: [
      { key: 'cameras-live', label: 'Live View', icon: 'pi pi-eye', route: 'cameras.live' },
      { key: 'cameras-recordings', label: 'Recordings', icon: 'pi pi-circle-fill', route: 'cameras.recordings' },
    ],
  },
  {
    key: 'access',
    label: 'Access Control',
    icon: 'pi pi-lock',
    route: 'access.index'
  },
  {
    key: 'reports',
    label: 'Reports',
    icon: 'pi pi-chart-bar',
    items: [
      { key: 'reports-daily', label: 'Daily Report', icon: 'pi pi-calendar', route: 'reports.daily' },
      { key: 'reports-monthly', label: 'Monthly Report', icon: 'pi pi-calendar', route: 'reports.monthly' },
      { key: 'reports-custom', label: 'Custom Report', icon: 'pi pi-sliders-h', route: 'reports.custom' },
    ],
  },
  {
    key: 'devices',
    label: 'Devices',
    icon: 'pi pi-tablet',
    route: 'devices.index'
  },
  {
    key: 'logs',
    label: 'System Logs',
    icon: 'pi pi-book',
    route: 'logs.index'
  },
  {
    key: 'settings',
    label: 'Settings',
    icon: 'pi pi-cog',
    items: [
      { key: 'settings-general', label: 'General Settings', icon: 'pi pi-cog', route: 'settings.general' },
      { key: 'settings-security', label: 'Security', icon: 'pi pi-shield', route: 'settings.security' },
      { key: 'settings-notifications', label: 'Notifications', icon: 'pi pi-bell', route: 'settings.notifications' },
      { key: 'settings-users', label: 'Users', icon: 'pi pi-users', route: 'admin.users.index' },
      { key: 'settings-roles', label: 'Roles', icon: 'pi pi-shield', route: 'admin.roles.index' },
    ],
  },
]);

const menuItems = computed(() => {
  return allMenuItems.value.filter(item => allowedSidebarItems.value.includes(item.key)).map(item => {
    // Filter submenu items based on permissions
    if (item.items && item.items.length > 0) {
      return {
        ...item,
        items: item.items.filter(subItem => allowedSidebarItems.value.includes(subItem.key))
      };
    }
    return item;
  });
});

const navigateTo = (item) => {
  if (item.route) {
    router.visit(route(item.route));
  }
};
</script>

<template>
  <div class="h-[calc(100vh-4rem)] flex shrink-0 bg-surface-50 text-surface-700 dark:bg-surface-950/85 dark:text-surface-200 transition-all duration-300 backdrop-blur-xl relative z-50" :class="expanded ? 'w-80' : 'w-20'">
    <div class="flex flex-col h-full w-full border-r border-surface-200 bg-gradient-to-b from-surface-50 to-surface-100 shadow-inner shadow-surface-200/60 dark:border-surface-700 dark:from-surface-950/90 dark:to-surface-900/80 dark:shadow-black/40">
      <!-- Menu Items Container -->
      <div class="flex-1 p-3 flex flex-col overflow-y-auto">
        <!-- Loop through all menu items -->
        <ul class="list-none p-0 m-0 flex flex-col gap-1">
          <li v-for="item in menuItems" :key="item.key" class="relative">
            <!-- Item without submenu -->
            <a
              v-if="!item.items"
              class="flex items-center cursor-pointer px-3 py-3 text-surface-600 hover:bg-surface-200/80 hover:text-primary-600 dark:text-surface-300 dark:hover:bg-surface-900/60 dark:hover:text-primary-200 rounded-2xl transition-colors duration-150"
              :class="[
                expanded ? 'justify-start gap-3' : 'justify-center',
                activeMenuItem === item.key ? 'bg-primary-500/20 text-primary-600 shadow-inner shadow-primary-200/40 dark:text-primary-50 dark:shadow-primary-950/40' : ''
              ]"
              :title="item.label"
              @click="navigateTo(item)"
            >
              <i :class="[item.icon, 'text-base']" />
              <span v-if="expanded" class="font-medium flex-1 tracking-wide uppercase text-xs">{{ item.label }}</span>
            </a>

            <!-- Item with submenu -->
            <div v-else class="relative">
              <!-- Parent menu item - click handler changes based on expanded state -->
              <a
                v-if="expanded"
                @click="toggleSubmenu(item.key)"
                class="flex items-center cursor-pointer px-3 py-3 text-surface-600 hover:bg-surface-200/80 dark:text-surface-300 dark:hover:bg-surface-900/60 rounded-2xl transition-colors duration-150 justify-between"
                :title="item.label"
              >
                <div class="flex items-center gap-3">
                  <i :class="[item.icon, 'text-base']" />
                  <span class="font-medium uppercase text-xs tracking-wide">{{ item.label }}</span>
                </div>
                <i class="pi pi-chevron-down text-sm text-surface-500 dark:text-surface-400 transition-transform duration-200" :class="{ 'rotate-180': isSubmenuOpen(item.key) }" />
              </a>
              
              <!-- Parent menu item for collapsed state with v-styleclass -->
              <a
                v-else
                v-styleclass="{ selector: '@next', enterFromClass: 'hidden', enterActiveClass: '', leaveToClass: 'hidden', leaveActiveClass: '', hideOnOutsideClick: true }"
                class="flex items-center cursor-pointer px-3 py-3 text-surface-600 hover:bg-surface-200/80 dark:text-surface-300 dark:hover:bg-surface-900/60 rounded-2xl transition-colors duration-150 justify-center"
                :title="item.label"
              >
                <i :class="[item.icon, 'text-base']" />
              </a>

              <!-- Submenu when EXPANDED: Inline UL with transition -->
              <ul
                v-if="expanded"
                v-show="isSubmenuOpen(item.key)"
                class="list-none m-0 p-0 pl-6 static bg-transparent overflow-hidden transition-all duration-200"
                :class="isSubmenuOpen(item.key) ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0'"
              >
                <li v-for="subitem in item.items" :key="subitem.key">
                  <a
                    class="flex items-center cursor-pointer px-3 py-2 gap-2 text-surface-600 hover:bg-surface-200/80 dark:text-surface-300 dark:hover:bg-surface-900/60 rounded-2xl transition-colors duration-150"
                    :class="activeMenuItem === subitem.key ? 'bg-primary-500/20 text-primary-600 dark:text-primary-50' : ''"
                    @click="navigateTo(subitem)"
                  >
                    <i :class="[subitem.icon, 'text-sm']" />
                    <span class="font-medium text-xs uppercase tracking-wide">{{ subitem.label }}</span>
                  </a>
                </li>
              </ul>

              <!-- Submenu when COLLAPSED: Floating DIV -->
              <div
                v-else
                class="hidden fixed min-w-[14rem] bg-surface-0/95 border border-surface-200 dark:bg-surface-900/95 dark:border-surface-700 rounded-2xl shadow-2xl p-3 z-[1000] backdrop-blur"
                style="left: calc(5rem + 0.5rem); top: auto;"
              >
                <div v-for="subitem in item.items" :key="subitem.key">
                  <a
                    class="flex items-center cursor-pointer px-3 py-2 gap-2 text-surface-600 hover:bg-surface-200/80 dark:text-surface-300 dark:hover:bg-surface-900/60 rounded-2xl transition-colors duration-150"
                    :class="activeMenuItem === subitem.key ? 'bg-primary-500/20 text-primary-600 dark:text-primary-50' : ''"
                    @click="navigateTo(subitem)"
                  >
                    <i :class="[subitem.icon, 'text-sm']" />
                    <span class="font-medium text-xs uppercase tracking-wide">{{ subitem.label }}</span>
                  </a>
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>

      <!-- User Section at Bottom -->
      <div class="mt-auto border-t border-surface/70">
        <slot name="user-section"></slot>
      </div>
    </div>
  </div>
</template>
