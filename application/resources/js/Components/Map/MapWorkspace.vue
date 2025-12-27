<script setup>
import { ref } from 'vue';
import MapMenu from './MapMenu.vue';

const props = defineProps({
  title: {
    type: String,
    default: 'Main Control Floor'
  }
});

const activeMenu = ref('actions');
const menuCollapsed = ref(false);

const statusBadges = [
  { label: 'OD3 Intake', time: '09:51 AM', tone: 'text-orange-500' },
  { label: 'OD8 UnitB', time: '09:49 AM', tone: 'text-surface-500' },
  { label: 'OD3 UnitA', time: '08:48 AM', tone: 'text-surface-500' }
];

const toggleMenu = () => {
  menuCollapsed.value = !menuCollapsed.value;
};
</script>

<template>
  <section class="panel-chrome flex h-full min-h-0 flex-col text-surface-900 dark:text-surface-50">
    <header class="flex flex-wrap items-center justify-between gap-4 px-6 pt-6">
      <div>
        <p class="text-[0.65rem] uppercase tracking-[0.4em] text-primary-300">Live Map</p>
        <h2 class="text-2xl font-semibold text-surface-900 dark:text-surface-0">{{ title }}</h2>
      </div>
      <div class="flex flex-wrap gap-3">
        <div
          v-for="badge in statusBadges"
          :key="badge.label"
          class="flex items-center gap-2 rounded-full border border-surface-200 bg-white/80 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-surface-600 dark:border-surface-600 dark:bg-surface-900/30 dark:text-surface-200"
        >
          <span :class="badge.tone">{{ badge.label }}</span>
          <span class="text-surface-500 dark:text-surface-400">{{ badge.time }}</span>
        </div>
      </div>
    </header>

    <div class="mt-4 flex-1 min-h-0 px-6 pb-6">
      <div class="flex h-full min-h-0 flex-col gap-4">
        <div class="canvas-shell flex-1 min-h-0">
          <div class="relative flex h-full min-h-0 flex-col justify-center overflow-hidden">
            <div class="absolute top-4 right-4 z-10 flex flex-col items-end gap-2">
              <div class="flex items-center gap-3">
                <MapMenu
                  v-if="!menuCollapsed"
                  id="map-menu-panel"
                  class="max-w-[32rem] flex-shrink-0"
                  orientation="horizontal"
                  variant="overlay"
                  :active-key="activeMenu"
                  @update:active-key="activeMenu = $event"
                />
                <button
                  type="button"
                  class="flex items-center gap-2 rounded-full bg-primary px-3 py-2 text-sm font-semibold text-primary-contrast shadow-lg shadow-primary/40 transition hover:brightness-105"
                  :aria-expanded="(!menuCollapsed).toString()"
                  aria-controls="map-menu-panel"
                  @click="toggleMenu"
                >
                  <span>{{ menuCollapsed ? 'Open Menu' : 'Collapse Menu' }}</span>
                  <span :class="['pi text-base', menuCollapsed ? 'pi-chevron-left' : 'pi-chevron-right']"></span>
                </button>
              </div>
            </div>
            <div class="flex h-full flex-col items-center justify-center gap-3 text-center">
              <span class="text-lg font-semibold text-surface-700 dark:text-surface-200">Map Canvas Placeholder</span>
              <span class="text-sm text-surface-500 dark:text-surface-400">SVG / Realtime view will render here</span>
            </div>
            <div class="pointer-events-none absolute bottom-4 left-4 rounded-full bg-white/90 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-primary-600 shadow dark:bg-surface-900/70 dark:text-primary-200">
              {{ activeMenu }} mode
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
