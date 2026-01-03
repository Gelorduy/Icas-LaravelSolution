<script setup>
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import MapToolbar from '@/Components/Map/MapToolbar.vue';
import MapMenu from '@/Components/Map/MapMenu.vue';
import ViewportNavigator from '@/Components/Map/ViewportNavigator.vue';
import LayerTogglePanel from '@/Components/Map/LayerTogglePanel.vue';
import DxfImportPanel from '@/Components/Map/DxfImportPanel.vue';
import MapCanvas from '@/Components/Map/MapCanvas.vue';
import { useMapStore } from '@/Stores/mapStore';

const mapStore = useMapStore();
const page = usePage();

const allowedMenuItems = computed(() => page.props.permissions?.allowedMapMenuItems || []);

const getDefaultMenuKey = () => {
  // Prefer 'layers' if manifest is ready and user has access
  if (mapStore.manifest && allowedMenuItems.value.includes('layers')) {
    return 'layers';
  }
  // Fall back to 'import' if available
  if (allowedMenuItems.value.includes('import')) {
    return 'import';
  }
  // Otherwise, use first allowed item
  return allowedMenuItems.value[0] || 'layers';
};

const manifestReady = computed(() => !!mapStore.manifest && !mapStore.error);
const activeMenuKey = ref(getDefaultMenuKey());
const menuCollapsed = ref(false);
const overlayOpen = ref(true);

const manifestOptionalPanels = new Set(['import']);

const overlayMetadata = {
  actions: {
    title: 'Action Center',
    description: 'Trigger coordinated responses or broadcast commands across connected devices.'
  },
  edit: {
    title: 'Edit Tools',
    description: 'Draft annotations, adjust geometry, and fine-tune saved waypoints.'
  },
  options: {
    title: 'Map Options',
    description: 'Switch canvases, update viewports, and adjust zoom presets.'
  },
  tools: {
    title: 'Field Tools',
    description: 'Launch measurement, trace, or diagnostics tools tailored to your role.'
  },
  layers: {
    title: 'Layer Console',
    description: 'Toggle operational overlays, heatmaps, and IoT telemetry feeds.'
  },
  viewports: {
    title: 'Viewport Navigator',
    description: 'Jump across curated perspectives and saved patrol routes.'
  },
  import: {
    title: 'DXF Import',
    description: 'Upload facility DXF/DFX files and convert them to SVG layers.'
  }
};

const defaultOverlayMeta = {
  title: 'Workspace Menu',
  description: 'Choose a tab to open contextual overlays and utilities for the map workspace.'
};

const canRenderActivePanel = computed(() => {
  if (!overlayOpen.value) {
    return false;
  }

  if (manifestReady.value) {
    return true;
  }

  return manifestOptionalPanels.has(activeMenuKey.value);
});

const activeOverlayMeta = computed(() => overlayMetadata[activeMenuKey.value] ?? defaultOverlayMeta);

const handleMenuSelection = (key) => {
  // Only allow selection if user has permission for this menu item
  if (allowedMenuItems.value.includes(key)) {
    activeMenuKey.value = key;
    overlayOpen.value = true;
    menuCollapsed.value = false;
  }
};

const collapseMenu = () => {
  menuCollapsed.value = true;
};

const expandMenu = () => {
  menuCollapsed.value = false;
};

const closeOverlay = () => {
  overlayOpen.value = false;
};

watch(manifestReady, (ready) => {
  if (!ready && allowedMenuItems.value.includes('import')) {
    activeMenuKey.value = 'import';
  } else if (ready && allowedMenuItems.value.includes('layers')) {
    activeMenuKey.value = 'layers';
  }
});

// Watch for changes in allowed items and reset to default if current key is not allowed
watch(allowedMenuItems, (items) => {
  if (!items.includes(activeMenuKey.value)) {
    activeMenuKey.value = getDefaultMenuKey();
  }
});
</script>

<template>
  <section class="panel-chrome flex h-full min-h-0 flex-col text-surface-900 dark:text-surface-50">
    <div class="relative flex-1 min-h-0 px-6 pb-6">
      <div class="relative h-full min-h-[520px] overflow-hidden rounded-3xl">
        <MapCanvas class="h-full" />

        <div class="absolute right-6 top-6 z-20">
          <transition
            enter-active-class="duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-2"
          >
            <div v-if="!menuCollapsed" class="flex items-center gap-2 rounded-full border border-white/40 bg-white/90 px-4 py-2 shadow-lg backdrop-blur dark:border-surface-700 dark:bg-surface-900/90">
              <MapMenu
                :activeKey="activeMenuKey"
                variant="overlay"
                @update:activeKey="handleMenuSelection"
              />
              <button
                type="button"
                class="rounded-full border border-surface-200 px-2 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-surface-500 hover:border-primary hover:text-primary dark:border-surface-600 dark:text-surface-100"
                @click="collapseMenu"
              >
                Hide
              </button>
            </div>
          </transition>
          <button
            v-if="menuCollapsed"
            type="button"
            class="rounded-full border border-white/40 bg-white/85 px-4 py-3 text-surface-700 shadow-lg backdrop-blur transition hover:border-primary hover:text-primary dark:border-surface-600 dark:bg-surface-900/85 dark:text-surface-100"
            @click="expandMenu"
          >
            <i class="pi pi-bars text-base" />
          </button>
        </div>
        <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-[0.35em] text-surface-500 dark:text-surface-300">
          <DxfImportPanel
            class="max-h-[360px] overflow-y-auto rounded-3xl border border-surface-200 bg-white/70 shadow-inner dark:border-surface-600 dark:bg-surface-900/70"
          />
        </div>

        <transition
          enter-active-class="duration-200 ease-out"
          enter-from-class="opacity-0 translate-x-4"
          enter-to-class="opacity-100 translate-x-0"
          leave-active-class="duration-150 ease-in"
          leave-from-class="opacity-100 translate-x-0"
          leave-to-class="opacity-0 translate-x-4"
        >

          <aside
            v-if="overlayOpen"
            class="absolute right-4 top-24 z-30 flex w-full max-w-[360px] flex-col gap-4 rounded-3xl border border-white/40 bg-white/95 p-4 shadow-2xl backdrop-blur dark:border-surface-700/70 dark:bg-surface-900/95"
          >
            <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-[0.35em] text-surface-500 dark:text-surface-300">
              {{ activeOverlayMeta.title }}
              <button
                type="button"
                class="rounded-full border border-surface-200 px-2 py-1 text-[0.65rem] font-bold tracking-[0.2em] text-surface-600 hover:border-primary hover:text-primary dark:border-surface-600 dark:text-surface-200"
                @click="closeOverlay"
              >
                Close
              </button>
            </div>
            <LayerTogglePanel
              v-show="canRenderActivePanel && activeMenuKey === 'layers'"
              class="max-h-[360px] overflow-y-auto rounded-3xl border border-surface-200 bg-white/70 shadow-inner dark:border-surface-600 dark:bg-surface-900/70"
            />
            <ViewportNavigator
              v-show="canRenderActivePanel && activeMenuKey === 'viewports'"
              class="max-h-[360px] overflow-y-auto rounded-3xl border border-surface-200 bg-white/70 shadow-inner dark:border-surface-600 dark:bg-surface-900/70"
            />
            <MapToolbar
              v-show="canRenderActivePanel && activeMenuKey === 'options'"
              class="max-h-[360px] overflow-y-auto rounded-3xl border border-surface-200 bg-white/70 shadow-inner dark:border-surface-600 dark:bg-surface-900/70"
            />
            <DxfImportPanel
              v-show="canRenderActivePanel && activeMenuKey === 'import'"
              class="max-h-[360px] overflow-y-auto rounded-3xl border border-surface-200 bg-white/70 shadow-inner dark:border-surface-600 dark:bg-surface-900/70"
            />
            <div
              v-if="!canRenderActivePanel"
              class="rounded-2xl border border-dashed border-surface-300 bg-white/60 p-4 text-sm text-surface-600 dark:border-surface-600 dark:bg-surface-900/40 dark:text-surface-200"
            >
              <p>
                {{ manifestReady ? activeOverlayMeta.description : 'Select a facility from the global header and choose a map to begin.' }}
              </p>
            </div>
          </aside>
        </transition>
      </div>

      <div v-if="mapStore.loading || mapStore.error || !manifestReady" class="pointer-events-none absolute inset-x-0 bottom-6 flex justify-center px-6">
        <div
          class="w-full max-w-xl rounded-3xl border border-surface-200 bg-surface-0/90 p-4 text-center text-sm text-surface-600 shadow-lg backdrop-blur dark:border-surface-700 dark:bg-surface-900/80 dark:text-surface-100"
        >
          <span v-if="mapStore.loading">Loading map data…</span>
          <span v-else-if="mapStore.error">{{ mapStore.error }}</span>
          <span v-else>Select a facility from the global header, then choose a map to begin.</span>
        </div>
      </div>
    </div>
  </section>
</template>
