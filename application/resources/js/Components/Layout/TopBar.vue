<script setup>
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import StyleClass from 'primevue/styleclass';
import Button from 'primevue/button';
import Badge from 'primevue/badge';
import Dropdown from 'primevue/dropdown';

const vStyleclass = StyleClass;
const $page = usePage();

const showAdminMenu = ref(false);

const props = defineProps({
  sidebarExpanded: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['toggle-sidebar']);

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

const goToProfile = () => {
  router.visit(route('profile.show'));
};
</script>

<template>
  <div class="h-16 surface-card border-b-2 border-surface flex items-center px-6 sticky top-0 z-40 shadow-sm">
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
          class="text-surface-700 dark:text-surface-0/80"
        />

        <!-- Logo Section -->
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
            <span class="text-primary-contrast font-bold text-lg">IC</span>
          </div>
          <span class="font-bold text-2xl text-surface-900 dark:text-surface-0">ICAS</span>
        </div>

        <!-- Site Dropdown -->
        <div class="flex items-center gap-2">
          <span class="text-sm font-semibold text-surface-700 dark:text-surface-200">Site:</span>
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
      <div class="flex items-center gap-2">
        <!-- Theme Toggle -->
        <Button 
          icon="pi pi-palette" 
          rounded 
          text 
          severity="secondary" 
          aria-label="Theme"
          class="text-surface-700 dark:text-surface-0/80"
        />

        <!-- Shopping Cart -->
        <Button 
          icon="pi pi-shopping-cart" 
          rounded 
          text 
          severity="secondary" 
          aria-label="Cart"
          class="text-surface-700 dark:text-surface-0/80"
        />

        <!-- Calendar -->
        <Button 
          icon="pi pi-calendar" 
          rounded 
          text 
          severity="secondary" 
          aria-label="Calendar"
          class="text-surface-700 dark:text-surface-0/80"
        />

        <!-- Notifications with Badge -->
        <div class="relative">
          <Button 
            icon="pi pi-inbox" 
            rounded 
            text 
            severity="secondary" 
            aria-label="Notifications"
            class="text-surface-700 dark:text-surface-0/80"
          />
          <Badge value="3" severity="danger" class="absolute top-0 right-0 transform translate-x-1/4 -translate-y-1/4" />
        </div>

        <!-- User Profile Menu -->
        <div class="h-full relative">
          <a
            @click="showAdminMenu = !showAdminMenu"
            class="cursor-pointer h-full inline-flex items-center text-surface-600 dark:text-surface-200 px-2 border-l-2 lg:border-l-0 lg:border-b-2 border-transparent hover:border-surface-500 dark:hover:border-surface-300 transition-colors duration-150 gap-2"
          >
            <img
              :src="`https://ui-avatars.com/api/?name=${encodeURIComponent($page.props.auth.user.name)}&background=random`" 
              class="w-8 h-8 rounded-full cursor-pointer" 
              :alt="$page.props.auth.user.name"
            />
            <span class="font-medium text-sm text-surface-900 dark:text-surface-0">{{ $page.props.auth.user.name }}</span>
            <i v-if="showAdminMenu==false" class="pi pi-angle-down text-base! leading-none" />
            <i v-if="showAdminMenu" class="pi pi-angle-up text-base! leading-none" />
          </a>
          <div v-show="showAdminMenu" class="absolute w-56 bg-surface-0 bg-white dark:bg-surface-900 right-0 top-full z-50 shadow-lg origin-top rounded-lg border-2 border-surface-200 dark:border-surface-700 mt-2">
            <ul class="list-none p-2 m-0">
              <li>
                <a
                  @click="goToProfile"
                  class="cursor-pointer h-full inline-flex items-center text-surface-600 dark:text-surface-200 border-l-2 border-transparent hover:border-surface-500 dark:hover:border-surface-300 transition-colors duration-150 px-4 py-3 gap-2 w-full rounded-lg"
                >
                  <span class="pi pi-user text-base" />
                  <span class="font-medium">Profile</span>
                </a>
              </li>
              <li>
                <a
                  @click="goToProfile"
                  class="cursor-pointer h-full inline-flex items-center text-surface-600 dark:text-surface-200 border-l-2 border-transparent hover:border-surface-500 dark:hover:border-surface-300 transition-colors duration-150 px-4 py-3 gap-2 w-full rounded-lg"
                >
                  <span class="pi pi-cog text-base" />
                  <span class="font-medium">Settings</span>
                </a>
              </li>
              <li>
                <a
                  @click="logout"
                  class="cursor-pointer h-full inline-flex items-center text-surface-600 dark:text-surface-200 border-l-2 border-transparent hover:border-surface-500 dark:hover:border-surface-300 transition-colors duration-150 px-4 py-3 gap-2 w-full rounded-lg"
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
