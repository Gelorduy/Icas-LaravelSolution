<script setup>
import { ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import Sidebar from '@/Components/Layout/Sidebar.vue';
import TopBar from '@/Components/Layout/TopBar.vue';

const props = defineProps({
  title: {
    type: String,
    default: 'ICAS'
  }
});

const sidebarExpanded = ref(false);
const activeMenuItem = ref('dashboard');
const $page = usePage();

const toggleSidebar = () => {
  sidebarExpanded.value = !sidebarExpanded.value;
};
</script>

<template>
  <div class="min-h-screen surface-ground">
    <Head :title="title" />
    
    <!-- TopBar Component -->
    <TopBar 
      :sidebar-expanded="sidebarExpanded"
      @toggle-sidebar="toggleSidebar"
    />
    
    <div class="flex">
      <!-- Sidebar Component -->
      <Sidebar
        :expanded="sidebarExpanded"
        :activeMenuItem="activeMenuItem"
        @update:expanded="sidebarExpanded = $event"
        @update:activeMenuItem="activeMenuItem = $event"
      >
        <template #user-section>
          <div class="p-3">
            <a class="flex items-center cursor-pointer p-3 hover:bg-surface-900/60 rounded-2xl transition-colors duration-150" :class="sidebarExpanded ? 'gap-3' : 'justify-center'">
              <img src="https://fqjltiegiezfetthbags.supabase.co/storage/v1/render/image/public/block.images/blocks/avatars/avatar-amyels.png" class="w-8 h-8 rounded-full" />
              <div v-if="sidebarExpanded" class="flex flex-col">
                <span class="font-semibold text-sm text-surface-0/90">{{ $page.props.auth.user.name }}</span>
                <span class="text-xs text-surface-300">{{ $page.props.auth.user.email }}</span>
              </div>
            </a>
          </div>
        </template>
      </Sidebar>

      <!-- Main Content Area -->
      <div class="flex-1 flex flex-col min-h-screen">
        <!-- Page Content -->
        <div class="p-6 surface-section">
          <slot />
        </div>
      </div>
    </div>
  </div>
</template>
