<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { useMapStore } from '@/Stores/mapStore';
import DeleteMapModal from '@/Components/Map/DeleteMapModal.vue';

const mapStore = useMapStore();
const page = usePage();

const selectedSite = computed(() => mapStore.selectedSite);
const availableMaps = computed(() => selectedSite.value?.maps || []);
const selectedMapId = computed(() => mapStore.selectedMapId);
const userPermissions = computed(() => page.props.permissions?.allowedMapMenuItems || []);
const canDeleteMap = computed(() => userPermissions.value.includes('delete'));

const deleteModalVisible = ref(false);
const mapToDelete = ref(null);
const deleting = ref(false);
const errorMessage = ref('');

const handleMapSelect = (mapId) => {
  if (mapId !== selectedMapId.value) {
    mapStore.selectMap(mapId);
  }
};

const showDeleteModal = (map, event) => {
  event.stopPropagation();
  mapToDelete.value = map;
  deleteModalVisible.value = true;
  errorMessage.value = '';
};

const handleDeleteConfirm = async () => {
  if (!mapToDelete.value) return;

  deleting.value = true;
  errorMessage.value = '';

  try {
    await axios.delete(
      route('api.maps.delete', { 
        site: selectedSite.value.id, 
        map: mapToDelete.value.id 
      })
    );

    // Remove map from store
    const updatedMaps = availableMaps.value.filter(m => m.id !== mapToDelete.value.id);
    mapStore.selectedSite.maps = updatedMaps;

    // If deleted map was selected, select first available map or clear selection
    if (selectedMapId.value === mapToDelete.value.id) {
      if (updatedMaps.length > 0) {
        mapStore.selectMap(updatedMaps[0].id);
      } else {
        mapStore.selectedMapId = null;
        mapStore.manifest = null;
      }
    }

    deleteModalVisible.value = false;
    mapToDelete.value = null;
  } catch (error) {
    console.error('Delete error:', error);
    errorMessage.value = error.response?.data?.message || 'Failed to delete map. Please try again.';
  } finally {
    deleting.value = false;
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

            <!-- Right Side Actions -->
            <div class="flex flex-col items-end gap-2 flex-shrink-0">
              <!-- Active Indicator -->
              <span
                v-if="map.is_active"
                class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-300"
              >
                Active
              </span>
              <span
                v-else
                class="inline-flex items-center rounded-full bg-surface-100 px-2 py-1 text-xs font-medium text-surface-600 dark:bg-surface-700 dark:text-surface-300"
              >
                Inactive
              </span>

              <!-- Delete Button -->
              <button
                v-if="canDeleteMap"
                type="button"
                @click="showDeleteModal(map, $event)"
                class="rounded p-1.5 text-surface-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20 dark:hover:text-red-400"
                title="Delete map"
              >
                <i class="pi pi-trash text-sm" />
              </button>
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

    <!-- Error Message -->
    <div
      v-if="errorMessage"
      class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20"
    >
      <p class="text-sm font-medium text-red-800 dark:text-red-200">
        {{ errorMessage }}
      </p>
    </div>

    <!-- Delete Confirmation Modal -->
    <DeleteMapModal
      v-model:visible="deleteModalVisible"
      :map-name="mapToDelete?.name || ''"
      :loading="deleting"
      @confirm="handleDeleteConfirm"
    />
  </div>
</template>

