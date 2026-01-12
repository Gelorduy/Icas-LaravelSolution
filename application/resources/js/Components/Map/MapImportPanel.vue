<script setup>
import { computed, ref } from 'vue';
import axios from 'axios';
import { useMapStore } from '@/Stores/mapStore';

const mapStore = useMapStore();

const uploading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const selectedFile = ref(null);
const mapName = ref('');

const selectedSiteId = computed(() => mapStore.selectedSiteId);

const handleFileChange = (event) => {
  const file = event.target.files?.[0];
  if (!file) return;

  selectedFile.value = file;
  
  // Extract filename without extension for map name
  const baseName = file.name.replace(/\.[^/.]+$/, '');
  mapName.value = baseName;
  
  // Clear any previous messages
  successMessage.value = '';
  errorMessage.value = '';
};

const handleUpload = async () => {
  if (!selectedFile.value || !selectedSiteId.value) {
    errorMessage.value = 'Please select a file and ensure a site is selected.';
    return;
  }

  uploading.value = true;
  successMessage.value = '';
  errorMessage.value = '';

  const formData = new FormData();
  formData.append('blueprint', selectedFile.value);
  formData.append('filename', mapName.value);

  try {
    const response = await axios.post(
      route('api.maps.import-map', { site: selectedSiteId.value }),
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      }
    );

    if (response.data?.map) {
      mapStore.upsertMap(response.data.map);
      mapStore.selectMap(response.data.map.id);
      successMessage.value = response.data.message || 'Map imported successfully.';
      resetPanelState();
    }
  } catch (error) {
    console.error('Import error:', error);
    if (error.response?.data?.message) {
      errorMessage.value = error.response.data.message;
    } else if (error.response?.data?.errors) {
      const firstError = Object.values(error.response.data.errors)[0];
      errorMessage.value = Array.isArray(firstError) ? firstError[0] : firstError;
    } else {
      errorMessage.value = 'Failed to import map. Please try again.';
    }
  } finally {
    uploading.value = false;
  }
};

const resetPanelState = () => {
  selectedFile.value = null;
  successMessage.value = '';
  errorMessage.value = '';
  // Keep the map name so user can see what was imported
};
</script>

<template>
  <div class="space-y-6">
    <!-- Description -->
    <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
      <p class="text-sm text-blue-900 dark:text-blue-100">
        Upload a facility blueprint or map. DXF/DFX files will be automatically converted to SVG format.
        SVG files will be imported directly without conversion.
      </p>
    </div>

    <!-- Map Name Input -->
    <div>
      <label for="map-name" class="mb-2 block text-sm font-medium text-surface-900 dark:text-surface-0">
        Map Name
      </label>
      <input
        id="map-name"
        v-model="mapName"
        type="text"
        maxlength="255"
        placeholder="Enter a name for this map"
        class="w-full rounded-lg border border-surface-300 px-4 py-2 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-surface-600 dark:bg-surface-800"
      />
    </div>

    <!-- File Input -->
    <div>
      <label for="file-input" class="mb-2 block text-sm font-medium text-surface-900 dark:text-surface-0">
        Select File
      </label>
      <input
        id="file-input"
        type="file"
        accept=".dxf,.dfx,.svg"
        @change="handleFileChange"
        class="w-full rounded-lg border border-surface-300 px-4 py-2 file:mr-4 file:rounded-md file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-contrast hover:file:bg-primary/90 dark:border-surface-600 dark:bg-surface-800"
      />
      <p class="mt-2 text-xs text-surface-500 dark:text-surface-400">
        Accepted: DXF/DFX/SVG files up to 50MB
      </p>
    </div>

    <!-- Selected File Display -->
    <div v-if="selectedFile" class="rounded-lg border border-surface-200 bg-surface-50 p-4 dark:border-surface-700 dark:bg-surface-800">
      <p class="text-sm font-medium text-surface-900 dark:text-surface-0">Selected File:</p>
      <p class="mt-1 text-sm text-surface-600 dark:text-surface-300">
        {{ selectedFile.name }} ({{ (selectedFile.size / 1024 / 1024).toFixed(2) }} MB)
      </p>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-3">
      <button
        type="button"
        :disabled="uploading || !selectedFile"
        @click="handleUpload"
        class="flex-1 rounded-lg bg-primary px-6 py-3 font-semibold text-primary-contrast transition-colors hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-50"
      >
        <span v-if="uploading">Uploading...</span>
        <span v-else>Upload</span>
      </button>
      <button
        type="button"
        @click="resetPanelState"
        class="rounded-lg border border-surface-300 bg-surface-0 px-6 py-3 font-semibold text-surface-700 transition-colors hover:bg-surface-100 dark:border-surface-600 dark:bg-surface-800 dark:text-surface-300 dark:hover:bg-surface-700"
      >
        Reset
      </button>
    </div>

    <!-- Success Message -->
    <div v-if="successMessage" class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
      <p class="text-sm font-medium text-green-800 dark:text-green-200">
        {{ successMessage }}
      </p>
    </div>

    <!-- Error Message -->
    <div v-if="errorMessage" class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
      <p class="text-sm font-medium text-red-800 dark:text-red-200">
        {{ errorMessage }}
      </p>
    </div>
  </div>
</template>
