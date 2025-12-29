<script setup>
import { computed } from 'vue';
import { useMapStore } from '@/Stores/mapStore';

const mapStore = useMapStore();

const deriveLayerKey = (layer) => layer?.key ?? `layer-${layer?.id}`;

const layerGroups = computed(() => {
  const layers = mapStore.manifest?.layers ?? [];
  return layers.reduce((groups, layer) => {
    const group = layer.style_preset?.group_label || 'Base Layers';
    if (!groups[group]) groups[group] = [];
    groups[group].push(layer);
    return groups;
  }, {});
});

const getActiveState = (layer) => {
  const key = deriveLayerKey(layer);
  return mapStore.layerVisibility[key] ?? (layer.default_visible ?? true);
};

const describeLayer = (layer) => layer.style_preset?.description ?? layer.layer_type;
</script>

<template>
  <div class="flex h-full flex-col">
    <header class="flex items-center justify-between border-b border-surface-200 px-4 py-3 text-sm font-semibold uppercase tracking-[0.3em] text-surface-500 dark:border-surface-700 dark:text-surface-300">
      Layers
      <span class="rounded-full bg-surface-200/70 px-3 py-1 text-[0.7rem] font-semibold text-surface-600 dark:bg-surface-700/60 dark:text-surface-100">
        {{ Object.values(layerGroups).reduce((count, list) => count + list.length, 0) }}
      </span>
    </header>
    <div class="flex-1 overflow-y-auto p-4">
      <div v-if="!Object.keys(layerGroups).length" class="rounded-2xl border border-dashed border-surface-300 px-3 py-4 text-center text-xs text-surface-500 dark:border-surface-600 dark:text-surface-300">
        No layers available for this map.
      </div>
      <div v-else class="space-y-5">
        <section v-for="(layers, groupName) in layerGroups" :key="groupName">
          <div class="mb-2 flex items-center justify-between text-xs font-semibold uppercase tracking-[0.3em] text-surface-500 dark:text-surface-300">
            <span>{{ groupName }}</span>
            <span class="text-[0.65rem] text-surface-400 dark:text-surface-500">{{ layers.length }} layers</span>
          </div>
          <ul class="space-y-2">
            <li v-for="layer in layers" :key="layer.id">
              <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-surface-200 bg-white/80 px-4 py-3 text-sm font-semibold text-surface-700 shadow-sm transition hover:border-primary hover:shadow-md dark:border-surface-600 dark:bg-surface-900/70 dark:text-surface-100">
                <input
                  type="checkbox"
                  class="h-4 w-4 rounded border-surface-300 text-primary focus:ring-primary"
                  :checked="getActiveState(layer)"
                  @change="mapStore.toggleLayerVisibility(layer)"
                />
                <div class="flex-1">
                  <p>{{ layer.display_name }}</p>
                  <p class="text-xs font-normal text-surface-500 dark:text-surface-400">{{ describeLayer(layer) }}</p>
                </div>
                <span class="text-[0.65rem] uppercase tracking-[0.3em] text-surface-400 dark:text-surface-500">{{ layer.layer_type }}</span>
              </label>
            </li>
          </ul>
        </section>
      </div>
    </div>
  </div>
</template>
