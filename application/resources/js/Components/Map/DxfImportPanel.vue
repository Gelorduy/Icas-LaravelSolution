<script setup>
import { ref, computed, watch } from 'vue';
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
  const [file] = event.target.files ?? [];
  selectedFile.value = file ?? null;
  if (file) {
    const basename = file.name.replace(/\.[^.]+$/, '');
    mapName.value = mapName.value || basename;
  } else {
    mapName.value = '';
  }
  successMessage.value = '';
  errorMessage.value = '';
};

watch(selectedSiteId, () => {
  successMessage.value = '';
  errorMessage.value = '';
});

const handleUpload = async () => {
  if (!selectedFile.value || !selectedSiteId.value) {
    errorMessage.value = 'Select a DXF/DFX file and a facility first.';
    return;
  }

  const siteId = selectedSiteId.value;
  const formData = new FormData();
  formData.append('blueprint', selectedFile.value);
  formData.append('filename', mapName.value?.trim() || selectedFile.value.name);

  uploading.value = true;
  successMessage.value = '';
  errorMessage.value = '';
  try {
    const { data } = await axios.post(route('api.maps.import-dxf', { site: siteId }), formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    const newMap = data?.map;
    successMessage.value = newMap?.svg_asset_path
      ? 'Upload complete. The blueprint has been converted and applied.'
      : 'Upload received. Conversion will continue in the background.';
    selectedFile.value = null;
    mapName.value = '';
    if (newMap) {
      mapStore.upsertMap(siteId, newMap);
      mapStore.selectMap(newMap.id);
    }
  } catch (error) {
    console.error(error);
    errorMessage.value = error.response?.data?.message ?? 'Upload failed. Please try again.';
  } finally {
    uploading.value = false;
  }
};

const resetPanelState = () => {
  selectedFile.value = null;
  successMessage.value = '';
  errorMessage.value = '';
};
</script>

<template>
  <div name="dxf_import" class="space-y-4">
    <p class="text-sm text-surface-600 dark:text-surface-200">
      Upload a DXF/DFX facility blueprint. ICAS will convert it to SVG and attach it to the selected facility.
    </p>
    <div class="space-y-2">
      <label class="text-xs font-semibold uppercase tracking-[0.3em] text-surface-500 dark:text-surface-400">Map Name</label>
      <input
        v-model="mapName"
        type="text"
        maxlength="255"
        placeholder="e.g. Intake Level or Pod A"
        class="w-full rounded-2xl border border-surface-200 px-4 py-2 text-sm text-surface-700 outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-surface-600 dark:bg-surface-900/70 dark:text-surface-100"
      />
    </div>
    <div class="space-y-2">
      <label class="text-xs font-semibold uppercase tracking-[0.3em] text-surface-500 dark:text-surface-400">DXF/DFX File</label>
      <input
        type="file"
        accept=".dxf,.dfx"
        class="w-full cursor-pointer rounded-2xl border border-dashed border-surface-300 px-4 py-3 text-sm text-surface-700 dark:border-surface-600 dark:bg-surface-900/70 dark:text-surface-100"
        @change="handleFileChange"
      />
    </div>
    <div class="space-y-2 text-xs text-surface-500 dark:text-surface-400">
      <p>Facility: <span class="font-semibold text-surface-700 dark:text-surface-100">{{ mapStore.selectedSite?.name ?? 'Select a site from the top bar' }}</span></p>
      <p>Accepted: DXF/DFX up to 50MB.</p>
    </div>
    <div class="flex gap-3">
      <button
        type="button"
        class="rounded-full bg-primary px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-primary-contrast shadow-lg disabled:opacity-50"
        :disabled="uploading || !selectedFile"
        @click="handleUpload"
      >
        {{ uploading ? 'Uploading…' : 'Upload & Convert' }}
      </button>
      <button
        type="button"
        class="rounded-full border border-surface-300 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-surface-500 hover:border-primary hover:text-primary dark:border-surface-600 dark:text-surface-200"
        @click="resetPanelState"
      >
        Reset
      </button>
    </div>
    <p v-if="successMessage" class="rounded-2xl border border-green-200 bg-green-50/80 px-3 py-2 text-xs text-green-700 dark:border-green-400/60 dark:bg-green-900/30 dark:text-green-200">{{ successMessage }}</p>
    <p v-if="errorMessage" class="rounded-2xl border border-red-200 bg-red-50/80 px-3 py-2 text-xs text-red-700 dark:border-red-400/60 dark:bg-red-900/30 dark:text-red-200">{{ errorMessage }}</p>
  </div>
</template>
