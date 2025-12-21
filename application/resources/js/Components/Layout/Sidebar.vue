<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import StyleClass from 'primevue/styleclass';

const vStyleclass = StyleClass;

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

const emit = defineEmits(['update:expanded', 'update:activeMenuItem']);

const toggleSidebar = () => {
  emit('update:expanded', !props.expanded);
};

// All menu items with icons and optional submenus
const menuItems = ref([
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
    key: 'users',
    label: 'User Management',
    icon: 'pi pi-users',
    route: 'users.index'
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
    ],
  },
]);

const navigateTo = (item) => {
  if (item.route) {
    router.visit(route(item.route));
    emit('update:activeMenuItem', item.key);
  }
};
</script>

<template>
  <div class="h-[calc(100vh-4rem)] flex shrink-0 bg-surface-950/85 text-surface-200 transition-all duration-300 backdrop-blur-xl" :class="expanded ? 'w-80' : 'w-20'">
    <div class="flex flex-col h-full w-full border-r border-surface bg-gradient-to-b from-surface-950/90 to-surface-900/80 shadow-inner shadow-black/40">
      <!-- Menu Items Container -->
      <div class="flex-1 p-3 flex flex-col overflow-y-auto overflow-x-visible">
        <!-- Loop through all menu items -->
        <ul class="list-none p-0 m-0 flex flex-col gap-1">
          <li v-for="item in menuItems" :key="item.key" class="relative">
            <!-- Item without submenu -->
            <a
              v-if="!item.items"
              class="flex items-center cursor-pointer px-3 py-3 text-surface-300 hover:bg-surface-900/60 hover:text-primary-200 rounded-2xl transition-colors duration-150"
              :class="[
                expanded ? 'justify-start gap-3' : 'justify-center',
                activeMenuItem === item.key ? 'bg-primary-500/20 text-primary-50 shadow-inner shadow-primary-950/40' : ''
              ]"
              :title="item.label"
              @click="navigateTo(item)"
            >
              <i :class="[item.icon, 'text-base']" />
              <span v-if="expanded" class="font-medium flex-1 tracking-wide uppercase text-xs">{{ item.label }}</span>
            </a>

            <!-- Item with submenu -->
            <div v-else class="relative">
              <a
                v-styleclass="{ selector: '@next', enterFromClass: 'hidden', leaveToClass: 'hidden', hideOnOutsideClick: true }"
                class="flex items-center cursor-pointer px-3 py-3 text-surface-300 hover:bg-surface-900/60 rounded-2xl transition-colors duration-150"
                :class="expanded ? 'justify-between' : 'justify-center'"
                :title="item.label"
              >
                <div class="flex items-center" :class="expanded ? 'gap-3' : ''">
                  <i :class="[item.icon, 'text-base']" />
                  <span v-if="expanded" class="font-medium uppercase text-xs tracking-wide">{{ item.label }}</span>
                </div>
                <i v-if="expanded" class="pi pi-chevron-down text-sm text-surface-400" />
              </a>

              <!-- Submenu List (Desktop: Floating when collapsed, Inline when expanded) -->
              <ul
                class="list-none m-0 p-0 hidden overflow-hidden transition-all duration-300 ease-in-out"
                :class="[
                  expanded 
                    ? 'pl-6 static bg-transparent' 
                    : 'absolute left-full top-0 ml-2 min-w-[14rem] bg-surface-900/95 border border-surface rounded-2xl shadow-2xl p-3 z-[100] backdrop-blur'
                ]"
              >
                <li v-for="subitem in item.items" :key="subitem.key">
                  <a
                    class="flex items-center cursor-pointer px-3 py-2 gap-2 text-surface-300 hover:bg-surface-900/60 rounded-2xl transition-colors duration-150"
                    :class="activeMenuItem === subitem.key ? 'bg-primary-500/20 text-primary-50' : ''"
                    @click="navigateTo(subitem)"
                  >
                    <i :class="[subitem.icon, 'text-sm']" />
                    <span class="font-medium text-xs uppercase tracking-wide">{{ subitem.label }}</span>
                  </a>
                </li>
              </ul>
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
