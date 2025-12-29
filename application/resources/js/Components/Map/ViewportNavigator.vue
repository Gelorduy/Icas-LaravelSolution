<script setup>
import { computed } from 'vue';
import { useMapStore } from '@/Stores/mapStore';

const mapStore = useMapStore();

const viewports = computed(() => mapStore.manifest?.viewports ?? []);
const activeViewportId = computed(() => mapStore.activeViewportId);

const formatBounds = (bounds) => {
  if (!bounds) return 'Full extents';
  const { width, height } = bounds;
  return `${Math.round(width * 100)}% × ${Math.round(height * 100)}%`;
};
</script>

<template>
  <div class="flex h-full flex-col">
    <header class="flex items-center justify-between border-b border-surface-200 px-4 py-3 text-sm font-semibold uppercase tracking-[0.3em] text-surface-500 dark:border-surface-700 dark:text-surface-300">
      Viewports
      <span class="rounded-full bg-surface-200/70 px-3 py-1 text-[0.7rem] font-semibold text-surface-600 dark:bg-surface-700/60 dark:text-surface-100">{{ viewports.length }}</span>
    </header>
    <div class="flex-1 overflow-y-auto p-4">
      <div v-if="!viewports.length" class="rounded-2xl border border-dashed border-surface-300 px-3 py-4 text-center text-xs text-surface-500 dark:border-surface-600 dark:text-surface-300">
        No viewports defined for this map yet.
      </div>
      <ul v-else class="space-y-3">
        <li v-for="viewport in viewports" :key="viewport.id">
          <button
            type="button"
            class="w-full rounded-2xl border px-4 py-3 text-left transition hover:border-primary hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
            :class="[
              viewport.id === activeViewportId
                ? 'border-primary bg-primary/5 text-primary-700 dark:border-primary-300 dark:bg-primary-300/10 dark:text-primary-100'
                : 'border-surface-200 bg-white/70 text-surface-700 dark:border-surface-600 dark:bg-surface-900/60 dark:text-surface-100'
            ]"
            @click="mapStore.setActiveViewport(viewport.id)"
          >
            <div class="flex items-center justify-between text-sm font-semibold">
              <span>{{ viewport.name }}</span>
              <span v-if="viewport.is_root" class="rounded-full bg-primary/10 px-2 py-0.5 text-[0.65rem] font-bold uppercase tracking-wide text-primary-600 dark:bg-primary-300/20 dark:text-primary-200">Root</span>
            </div>
            <p class="mt-1 text-xs text-surface-500 dark:text-surface-300">{{ formatBounds(viewport.bounds) }}</p>
            <p v-if="viewport.layer_overrides" class="mt-1 text-[0.65rem] uppercase tracking-[0.3em] text-surface-400 dark:text-surface-500">
              {{ Object.keys(viewport.layer_overrides).length }} overrides
            </p>
          </button>
        </li>
      </ul>
    </div>
  </div>
</template>
