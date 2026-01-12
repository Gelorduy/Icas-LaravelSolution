<script setup>
import { computed } from 'vue';
import { useMapStore } from '@/Stores/mapStore';

const mapStore = useMapStore();

const selectedSite = computed(() => mapStore.selectedSite);
const availableMaps = computed(() => selectedSite.value?.maps || []);
const selectedMapId = computed(() => mapStore.selectedMapId);

const handleMapSelect = (mapId) => {
  if (mapId !== selectedMapId.value) {
    mapStore.selectMap(mapId);
  }
};

const getMapStatusColor = (map) => {
  if (map.conversion_status === 'completed') {
    return 'text-green-600 dark:text-green-400';
  }
  if (map.conversion_status === 'failed') {
    return 'text-red-600 dark:text-red-400';
  }
  if (map.conversion_status === 'queued' || map.conversion_status === 'processing') {
    return 'text-yellow-600 dark:text-yellow-400';
  }
  return 'text-surface-500 dark:text-surface-400';
};

const getMapStatusIcon = (map) => {
  if (map.conversion_status === 'completed') {
    return 'pi pi-check-circle';
  }
  if (map.conversion_status === 'failed') {
    return 'pi pi-times-circle';
  }
  if (map.conversion_status === 'queued' || map.conversion_status === 'processing') {
    return 'pi pi-spin pi-spinner';
  }
  return 'pi pi-circle';
};

const getMapStatusLabel = (map) => {
  const labels = {
    completed: 'Ready',
    failed: 'Failed',
    queued: 'Queued',
    processing: 'Processing',
  };
  return labels[map.conversion_status] || 'Unknown';
};
</script>

<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100">
            Current Site
          </h3>
          <p class="mt-1 text-lg font-bold text-blue-700 dark:text-blue-300">
            {{ selectedSite?.name || 'No site selected' }}
          </p>
        </div>
        <i class="pi pi-map text-2xl text-blue-600 dark:text-blue-400" />
      </div>
    </div>

    <!-- Maps List -->
    <div v-if="availableMaps.length > 0" class="space-y-2">
      <h4 class="text-xs font-semibold uppercase tracking-wider text-surface-500 dark:text-surface-400">
        Available Maps ({{ availableMaps.length }})
      </h4>
      
      <div class="space-y-2">
        <button
          v-for="map in availableMaps"
          :key="map.id"
          type="button"
          @click="handleMapSelect(map.id)"
          class="w-full rounded-lg border p-4 text-left transition-all hover:shadow-md"
          :class="[
            map.id === selectedMapId
              ? 'border-primary bg-primary/5 shadow-sm dark:bg-primary/10'
              : 'border-surface-200 bg-surface-0 hover:border-primary/50 dark:border-surface-700 dark:bg-surface-800'
          ]"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
              <!-- Map Name -->
              <div class="flex items-center gap-2">
                <h5 class="truncate text-base font-semibold text-surface-900 dark:text-surface-0">
                  {{ map.name }}
                </h5>
                <i
                  v-if="map.id === selectedMapId"
                  class="pi pi-check-circle text-primary"
                />
              </div>
              
              <!-- Map Slug/ID -->
              <p class="mt-1 text-xs text-surface-500 dark:text-surface-400">
                ID: {{ map.slug || map.id }}
              </p>
              
              <!-- Status Badge -->
              <div class="mt-2 flex items-center gap-2">
                <i :class="[getMapStatusIcon(map), getMapStatusColor(map), 'text-sm']" />
                <span :class="['text-xs font-medium', getMapStatusColor(map)]">
                  {{ getMapStatusLabel(map) }}
                </span>
              </div>

              <!-- Conversion Notes (if failed) -->
              <p
                v-if="map.conversion_status === 'failed' && map.conversion_notes"
                class="mt-2 text-xs text-red-600 dark:text-red-400"
              >
                {{ map.conversion_notes }}
              </p>
            </div>

            <!-- Active Indicator -->
            <div
              v-if="map.is_active"
              class="flex-shrink-0"
            >
              <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300">
                Active
              </span>
            </div>
            <div
              v-else
              class="flex-shrink-0"
            >
              <span class="inline-flex items-center rounded-full bg-surface-100 px-2 py-1 text-xs font-medium text-surface-600 dark:bg-surface-700 dark:text-surface-300">
                Inactive
              </span>
            </div>
          </div>
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-else
      class="rounded-lg border-2 border-dashed border-surface-300 bg-surface-50 p-8 text-center dark:border-surface-600 dark:bg-surface-800/50"
    >
      <i class="pi pi-map mb-3 text-4xl text-surface-400 dark:text-surface-500" />
      <p class="text-sm font-medium text-surface-700 dark:text-surface-300">
        No maps available
      </p>
      <p class="mt-1 text-xs text-surface-500 dark:text-surface-400">
        Import a map to get started
      </p>
    </div>
  </div>
</template>
