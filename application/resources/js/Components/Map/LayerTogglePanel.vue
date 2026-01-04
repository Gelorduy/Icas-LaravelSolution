<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useMapStore } from '@/Stores/mapStore';
import axios from 'axios';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import MultiSelect from 'primevue/multiselect';
import Textarea from 'primevue/textarea';
import Checkbox from 'primevue/checkbox';

const mapStore = useMapStore();
const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const editingLayer = ref(null);
const formData = ref({
  display_name: '',
  layer_type: 'overlay',
  element_types: [],
  parent_layer_id: null,
  related_layers: [],
  default_visible: true,
  z_index: 10,
  description: ''
});

const layerTypes = [
  { label: 'Overlay', value: 'overlay' },
  { label: 'Base', value: 'base' },
  { label: 'Annotation', value: 'annotation' },
  { label: 'Data', value: 'data' }
];

const elementTypes = [
  { label: 'SVG Icon', value: 'svg_icon' },
  { label: 'Text Label', value: 'text' },
  { label: 'Marker', value: 'marker' },
  { label: 'Polygon', value: 'polygon' },
  { label: 'Line', value: 'line' },
  { label: 'Circle', value: 'circle' }
];

const availableLayers = computed(() => {
  return (mapStore.manifest?.layers ?? []).map(layer => ({
    label: layer.display_name,
    value: layer.id
  }));
});

const createLayer = async () => {
  try {
    if (!mapStore.selectedMapId) {
      alert('No map selected');
      return;
    }

    const response = await axios.post(`/api/maps/${mapStore.selectedMapId}/layers`, {
      key: formData.value.display_name.toLowerCase().replace(/\s+/g, '-'),
      display_name: formData.value.display_name,
      layer_type: formData.value.layer_type,
      element_types: formData.value.element_types,
      parent_layer_id: formData.value.parent_layer_id,
      related_layers: formData.value.related_layers,
      default_visible: formData.value.default_visible,
      z_index: formData.value.z_index,
      style_preset: {
        description: formData.value.description
      },
      data_source: {}
    });
    
    console.log('Layer created successfully:', response.data);
    
    showCreateDialog.value = false;
    formData.value = {
      display_name: '',
      layer_type: 'overlay',
      element_types: [],
      parent_layer_id: null,
      related_layers: [],
      default_visible: true,
      z_index: 10,
      description: ''
    };
    
    // Refresh map data
    router.reload({ only: ['manifest'] });
  } catch (error) {
    console.error('Failed to create layer:', error);
    alert(`Error creating layer: ${error.response?.data?.message || error.message}`);
  }
};

const openEditLayer = (layer) => {
  editingLayer.value = layer;
  // Navigate to layer editor page
  router.visit(route('map.layers.edit', { map: mapStore.selectedMapId, layer: layer.id }));
};

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
      <div class="flex items-center gap-2">
        <span>Layers</span>
        <span class="rounded-full bg-surface-200/70 px-3 py-1 text-[0.7rem] font-semibold text-surface-600 dark:bg-surface-700/60 dark:text-surface-100">
          {{ Object.values(layerGroups).reduce((count, list) => count + list.length, 0) }}
        </span>
      </div>
      <Button 
        icon="pi pi-plus" 
        size="small" 
        rounded 
        text
        severity="secondary"
        @click="showCreateDialog = true"
        v-tooltip.left="'Create Layer'"
      />
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
                <Button 
                  icon="pi pi-pencil" 
                  size="small" 
                  text
                  rounded
                  severity="secondary"
                  @click.stop="openEditLayer(layer)"
                  v-tooltip.left="'Edit Layer'"
                />
              </label>
            </li>
          </ul>
        </section>
      </div>
    </div>

    <!-- Create Layer Dialog -->
    <Dialog 
      v-model:visible="showCreateDialog" 
      header="Create New Layer"
      :modal="true"
      :style="{ width: '36rem' }"
      :dismissableMask="true"
    >
      <div class="space-y-4">
        <div>
          <label for="display_name" class="block text-sm font-medium mb-2">Layer Name</label>
          <InputText 
            id="display_name" 
            v-model="formData.display_name" 
            class="w-full"
            placeholder="e.g., Fire Alarms, Room Labels"
          />
        </div>

        <div>
          <label for="layer_type" class="block text-sm font-medium mb-2">Layer Type</label>
          <Dropdown 
            id="layer_type" 
            v-model="formData.layer_type" 
            :options="layerTypes"
            optionLabel="label"
            optionValue="value"
            class="w-full"
          />
        </div>

        <div>
          <label for="element_types" class="block text-sm font-medium mb-2">Element Types</label>
          <MultiSelect 
            id="element_types" 
            v-model="formData.element_types" 
            :options="elementTypes"
            optionLabel="label"
            optionValue="value"
            placeholder="Select element types"
            class="w-full"
            display="chip"
          />
          <small class="text-surface-500">Types of elements this layer will contain</small>
        </div>

        <div>
          <label for="parent_layer" class="block text-sm font-medium mb-2">Parent Layer (Optional)</label>
          <Dropdown 
            id="parent_layer" 
            v-model="formData.parent_layer_id" 
            :options="availableLayers"
            optionLabel="label"
            optionValue="value"
            placeholder="Select parent layer"
            class="w-full"
            :showClear="true"
          />
          <small class="text-surface-500">Layer that controls this layer's visibility</small>
        </div>

        <div>
          <label for="related_layers" class="block text-sm font-medium mb-2">Related Layers (Optional)</label>
          <MultiSelect 
            id="related_layers" 
            v-model="formData.related_layers" 
            :options="availableLayers"
            optionLabel="label"
            optionValue="value"
            placeholder="Select related layers"
            class="w-full"
            display="chip"
          />
          <small class="text-surface-500">Layers this one depends on or controls</small>
        </div>

        <div>
          <label for="description" class="block text-sm font-medium mb-2">Description (Optional)</label>
          <Textarea 
            id="description" 
            v-model="formData.description" 
            rows="3"
            class="w-full"
            placeholder="Describe the purpose of this layer..."
          />
        </div>

        <div class="flex items-center gap-4">
          <div>
            <label for="z_index" class="block text-sm font-medium mb-2">Z-Index</label>
            <InputText 
              id="z_index" 
              v-model.number="formData.z_index" 
              type="number"
              class="w-24"
            />
          </div>
          <div class="flex items-center gap-2 mt-6">
            <Checkbox 
              id="default_visible" 
              v-model="formData.default_visible" 
              :binary="true"
            />
            <label for="default_visible" class="text-sm font-medium">Visible by default</label>
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-4">
          <Button 
            label="Cancel" 
            severity="secondary" 
            @click="showCreateDialog = false"
          />
          <Button 
            label="Create Layer" 
            @click="createLayer"
            :disabled="!formData.display_name"
          />
        </div>
      </div>
    </Dialog>
  </div>
</template>
