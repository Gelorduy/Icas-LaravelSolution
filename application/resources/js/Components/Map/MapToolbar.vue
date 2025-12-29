<script setup>
import { computed } from 'vue';
import { useMapStore } from '@/Stores/mapStore';

const mapStore = useMapStore();

const maps = computed(() => mapStore.selectedSite?.maps ?? []);
const activeViewport = computed(() => mapStore.activeViewport);
const viewportOptions = computed(() => mapStore.manifest?.viewports ?? []);
const layerCount = computed(() => mapStore.manifest?.layers?.length ?? 0);
const viewportCount = computed(() => mapStore.manifest?.viewports?.length ?? 0);
const workspaceDisabled = computed(() => !mapStore.manifest);

const selectedMapId = computed({
  get: () => mapStore.selectedMapId,
  set: (value) => mapStore.selectMap(value)
});

const zoomDisplay = computed(() => `${Math.round((mapStore.zoom ?? 1) * 100)}%`);

const mapContextHint = computed(() => {
  const siteName = mapStore.selectedSite?.name;
  if (siteName) {
    return `Facility · ${siteName}`;
  }
  return 'Use the global header to switch facilities.';
});

const activeViewportSummary = computed(() => {
  if (!activeViewport.value) {
    return 'Select a viewport to load saved camera bounds.';
  }
  const refresh = activeViewport.value.refresh_interval
    ? `refresh ${activeViewport.value.refresh_interval}s`
    : 'live stream';
  return `${activeViewport.value.name} · ${refresh}`;
});

const handleViewportChange = (event) => {
  const id = Number(event.target.value);
  if (Number.isFinite(id)) {
    mapStore.setActiveViewport(id);
  }
};

const handleResetView = () => {
  mapStore.setZoom(1);
  mapStore.setPan({ x: 0, y: 0 });
};
</script>

<template>
  <header class="rounded-3xl border border-surface-200 bg-white/80 px-5 py-4 shadow-sm backdrop-blur dark:border-surface-700 dark:bg-surface-900/70">
      <div class="flex flex-col gap-5">
        <div class="flex flex-col gap-2">
        <label class="text-xs font-semibold uppercase tracking-[0.3em] text-surface-500 dark:text-surface-400">Map</label>
        <select
          v-model="selectedMapId"
          class="rounded-2xl border border-surface-200 px-3 py-2 text-sm text-surface-800 outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-surface-700 dark:bg-surface-900 dark:text-surface-50"
        >
          <option v-if="!maps.length" disabled value="">No maps available</option>
          <option v-for="map in maps" :key="map.id" :value="map.id">
            {{ map.name }}
            <span v-if="map.floor_label"> · {{ map.floor_label }}</span>
          </option>
        </select>
        <div class="flex items-center justify-between text-[0.7rem] uppercase tracking-[0.3em] text-surface-400 dark:text-surface-500">
          <span>{{ mapContextHint }}</span>
          <span class="hidden lg:inline">{{ maps.length }} maps</span>
        </div>
      </div>

      <div class="flex flex-col gap-2">
        <label class="text-xs font-semibold uppercase tracking-[0.3em] text-surface-500 dark:text-surface-400">Viewport</label>
        <select
          :value="mapStore.activeViewportId ?? ''"
          class="rounded-2xl border border-surface-200 px-3 py-2 text-sm text-surface-800 outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-surface-700 dark:bg-surface-900 dark:text-surface-50"
          @change="handleViewportChange"
        >
          <option v-if="!viewportOptions.length" disabled value="">No viewports</option>
          <option
            v-for="viewport in viewportOptions"
            :key="viewport.id"
            :value="viewport.id"
          >
            {{ viewport.name }}
            <span v-if="viewport.is_root" class="text-[0.65rem] uppercase text-primary-500"> (root)</span>
          </option>
        </select>
        <p class="text-xs text-surface-500 dark:text-surface-400">{{ activeViewportSummary }}</p>
        <div class="flex flex-wrap gap-3 text-[0.65rem] uppercase tracking-[0.3em] text-surface-400 dark:text-surface-500">
          <span>{{ viewportCount }} viewports</span>
          <span>{{ layerCount }} layers</span>
        </div>
      </div>

      <div class="flex flex-col gap-3">
        <label class="text-xs font-semibold uppercase tracking-[0.3em] text-surface-500 dark:text-surface-400">Zoom</label>
        <div class="flex items-center gap-3 rounded-2xl border border-surface-200 px-3 py-2 text-sm dark:border-surface-700">
          <button
            type="button"
            class="rounded-full border border-surface-200 px-2 py-1 text-xs font-semibold text-surface-700 transition hover:border-primary hover:text-primary disabled:opacity-40 dark:border-surface-600 dark:text-surface-200"
            :disabled="workspaceDisabled"
            @click="mapStore.adjustZoom(-0.1)"
          >
            -
          </button>
          <span class="font-semibold text-surface-800 dark:text-surface-50">{{ zoomDisplay }}</span>
          <button
            type="button"
            class="rounded-full border border-surface-200 px-2 py-1 text-xs font-semibold text-surface-700 transition hover:border-primary hover:text-primary disabled:opacity-40 dark:border-surface-600 dark:text-surface-200"
            :disabled="workspaceDisabled"
            @click="mapStore.adjustZoom(0.1)"
          >
            +
          </button>
          <button
            type="button"
            class="ml-auto rounded-full border border-surface-200 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-surface-600 transition hover:border-primary hover:text-primary disabled:opacity-40 dark:border-surface-600 dark:text-surface-300"
            :disabled="workspaceDisabled"
            @click="handleResetView"
          >
            Reset
          </button>
        </div>
      </div>
    </div>
  </header>
</template>
