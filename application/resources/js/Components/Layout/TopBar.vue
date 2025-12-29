<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Badge from 'primevue/badge';
import Dropdown from 'primevue/dropdown';
import PalettePanel from '@/Components/Layout/PalettePanel.vue';
import { getThemeMode, toggleThemeMode } from '@/utils/themeMode';
import { useMapStore } from '@/Stores/mapStore';
const $page = usePage();

const showAdminMenu = ref(false);
const palettePanelOpen = ref(false);
const paletteTriggerRef = ref(null);
const themeMode = ref(getThemeMode());
const themeIcon = computed(() => (themeMode.value === 'dark' ? 'pi pi-sun' : 'pi pi-moon'));
const themeLabel = computed(() => (themeMode.value === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'));

const props = defineProps({
  sidebarExpanded: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['toggle-sidebar']);

const mapStore = useMapStore();

// Sample site data (fallback when global sites are not yet loaded)
const fallbackSites = ref([
  { name: 'Monroe County Jail', code: 'MCJ' },
  { name: 'City Center Facility', code: 'CCF' },
  { name: 'North Campus', code: 'NC' },
]);
const fallbackSelectedSite = ref(fallbackSites.value[0]);

const hasGlobalSites = computed(() => (mapStore.sites?.length ?? 0) > 0);
const siteOptions = computed(() => (hasGlobalSites.value ? mapStore.sites : fallbackSites.value));

const selectedSiteModel = computed({
  get: () => {
    if (hasGlobalSites.value) {
      return mapStore.selectedSite ?? siteOptions.value[0] ?? null;
    }
    return fallbackSelectedSite.value;
  },
  set: (site) => {
    if (hasGlobalSites.value && site?.id) {
      mapStore.selectSite(site.id);
      return;
    }
    fallbackSelectedSite.value = site;
  },
});

const logout = () => {
  router.post(route('logout'));
};

const goToProfile = () => {
  router.visit(route('profile.show'));
};

const handleGlobalClick = (event) => {
  if (!paletteTriggerRef.value) {
    return;
  }

  if (!paletteTriggerRef.value.contains(event.target)) {
    palettePanelOpen.value = false;
  }
};

const handleThemeToggle = () => {
  themeMode.value = toggleThemeMode();
};

onMounted(() => {
  document.addEventListener('click', handleGlobalClick);
  themeMode.value = getThemeMode();
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleGlobalClick);
});
</script>

<template>
  <div class="h-16 border-b border-surface-200 bg-gradient-to-r from-surface-50 via-surface-100 to-surface-200/90 text-surface-800 dark:border-surface-700 dark:from-surface-950/90 dark:via-surface-900/90 dark:to-surface-800/85 dark:text-surface-0/90 flex items-center px-6 sticky top-0 z-40 shadow-xl shadow-surface-300/30 dark:shadow-black/40 backdrop-blur">
    <div class="flex items-center justify-between w-full gap-6">
      <!-- Left: Sidebar Toggle, Logo and Site Selector -->
      <div class="flex items-center gap-4">
        <!-- Sidebar Toggle Button -->
        <Button 
          icon="pi pi-bars" 
          text 
          rounded
          severity="secondary"
          @click="emit('toggle-sidebar')"
          class="text-surface-700 hover:text-primary-600 dark:text-surface-0/80 dark:hover:text-primary-200"
        />

        <!-- Logo Section -->
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-primary-600/80 rounded-xl flex items-center justify-center shadow-inner shadow-primary-800/30 dark:shadow-black/30">
            <span class="text-primary-contrast font-bold text-lg tracking-[0.2em]">IC</span>
          </div>
          <span class="font-bold text-2xl tracking-[0.3em] uppercase text-surface-800 dark:text-surface-0/80">ICAS</span>
        </div>

        <!-- Site Dropdown -->
        <div class="flex items-center gap-2">
          <span class="text-[0.65rem] font-semibold uppercase tracking-[0.4em] text-surface-600 dark:text-surface-300">Site</span>
          <Dropdown 
            v-model="selectedSiteModel" 
            :options="siteOptions" 
            optionLabel="name"
            placeholder="Select a Site"
            class="w-64"
          />
        </div>
      </div>

      <!-- Right: Action Icons and User Menu -->
      <div class="flex items-center gap-2 text-surface-700 dark:text-surface-100">
        <!-- Mode Toggle -->
        <Button 
          :icon="themeIcon" 
          rounded 
          text 
          severity="secondary" 
          :aria-label="themeLabel"
          class="text-surface-700 hover:text-primary-600 dark:text-surface-100 dark:hover:text-primary-200"
          @click="handleThemeToggle"
        />

        <!-- Theme Palette -->
        <div ref="paletteTriggerRef" class="relative">
          <Button 
            icon="pi pi-palette" 
            rounded 
            text 
            severity="secondary" 
            aria-label="Theme Palette"
            aria-haspopup="dialog"
            :aria-expanded="palettePanelOpen"
            class="text-surface-700 hover:text-primary-600 dark:text-surface-100 dark:hover:text-primary-200"
            @click.stop="palettePanelOpen = !palettePanelOpen"
          />
          <transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-1"
          >
            <PalettePanel v-if="palettePanelOpen" @close="palettePanelOpen = false" />
          </transition>
        </div>

        <!-- Shopping Cart -->
        <Button 
          icon="pi pi-shopping-cart" 
          rounded 
          text 
          severity="secondary" 
          aria-label="Cart"
          class="text-surface-700 hover:text-primary-600 dark:text-surface-100 dark:hover:text-primary-200"
        />

        <!-- Calendar -->
        <Button 
          icon="pi pi-calendar" 
          rounded 
          text 
          severity="secondary" 
          aria-label="Calendar"
          class="text-surface-700 hover:text-primary-600 dark:text-surface-100 dark:hover:text-primary-200"
        />

        <!-- Notifications with Badge -->
        <div class="relative">
          <Button 
            icon="pi pi-inbox" 
            rounded 
            text 
            severity="secondary" 
            aria-label="Notifications"
            class="text-surface-700 hover:text-primary-600 dark:text-surface-100 dark:hover:text-primary-200"
          />
          <Badge value="3" severity="danger" class="absolute top-0 right-0 transform translate-x-1/4 -translate-y-1/4" />
        </div>

        <!-- User Profile Menu -->
        <div class="h-full relative">
          <a
            @click="showAdminMenu = !showAdminMenu"
            class="cursor-pointer h-full inline-flex items-center text-surface-700 dark:text-surface-100/80 px-2 border-l-2 lg:border-l-0 lg:border-b-2 border-transparent hover:border-surface transition-colors duration-150 gap-2"
          >
            <img
              :src="`https://ui-avatars.com/api/?name=${encodeURIComponent($page.props.auth.user.name)}&background=random`" 
              class="w-8 h-8 rounded-full cursor-pointer" 
              :alt="$page.props.auth.user.name"
            />
            <span class="font-medium text-sm text-surface-800 dark:text-surface-0/90">{{ $page.props.auth.user.name }}</span>
            <i v-if="showAdminMenu==false" class="pi pi-angle-down text-base! leading-none" />
            <i v-if="showAdminMenu" class="pi pi-angle-up text-base! leading-none" />
          </a>
          <div v-show="showAdminMenu" class="absolute w-56 bg-surface-0/95 border border-surface-200 dark:bg-surface-900/95 dark:border-surface-700 backdrop-blur-xl right-0 top-full z-50 shadow-2xl shadow-surface-400/40 origin-top rounded-2xl dark:shadow-black/50 mt-2">
            <ul class="list-none p-2 m-0">
              <li>
                <a
                  @click="goToProfile"
                  class="cursor-pointer h-full inline-flex items-center text-surface-700 dark:text-surface-200 border-l-2 border-transparent hover:border-primary-500 dark:hover:border-primary-400 transition-colors duration-150 px-4 py-3 gap-2 w-full rounded-xl hover:bg-surface-100/50 dark:hover:bg-surface-800/50"
                >
                  <span class="pi pi-user text-base" />
                  <span class="font-medium">Profile</span>
                </a>
              </li>
              <li>
                <a
                  @click="goToProfile"
                  class="cursor-pointer h-full inline-flex items-center text-surface-700 dark:text-surface-200 border-l-2 border-transparent hover:border-primary-500 dark:hover:border-primary-400 transition-colors duration-150 px-4 py-3 gap-2 w-full rounded-xl hover:bg-surface-100/50 dark:hover:bg-surface-800/50"
                >
                  <span class="pi pi-cog text-base" />
                  <span class="font-medium">Settings</span>
                </a>
              </li>
              <li>
                <a
                  @click="logout"
                  class="cursor-pointer h-full inline-flex items-center text-surface-700 dark:text-surface-200 border-l-2 border-transparent hover:border-primary-500 dark:hover:border-primary-400 transition-colors duration-150 px-4 py-3 gap-2 w-full rounded-xl hover:bg-surface-100/50 dark:hover:bg-surface-800/50"
                >
                  <span class="pi pi-sign-out text-base" />
                  <span class="font-medium">Sign Out</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
