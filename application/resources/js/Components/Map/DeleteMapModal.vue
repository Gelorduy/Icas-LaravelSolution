<script setup>
import { ref } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';

const props = defineProps({
  visible: {
    type: Boolean,
    required: true
  },
  mapName: {
    type: String,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:visible', 'confirm']);

const handleClose = () => {
  emit('update:visible', false);
};

const handleConfirm = () => {
  emit('confirm');
};
</script>

<template>
  <Dialog
    :visible="visible"
    modal
    :closable="!loading"
    :dismissableMask="!loading"
    @update:visible="handleClose"
    class="w-full max-w-md"
  >
    <template #header>
      <div class="flex items-center gap-3">
        <i class="pi pi-exclamation-triangle text-2xl text-red-600" />
        <span class="text-xl font-bold">Delete Map</span>
      </div>
    </template>

    <div class="space-y-4">
      <p class="text-surface-700 dark:text-surface-300">
        Are you sure you want to delete the map <strong class="font-semibold text-surface-900 dark:text-surface-0">"{{ mapName }}"</strong>?
      </p>

      <div class="rounded-lg border-l-4 border-red-500 bg-red-50 p-4 dark:bg-red-900/20">
        <p class="text-sm font-medium text-red-800 dark:text-red-200">
          This action cannot be undone!
        </p>
        <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700 dark:text-red-300">
          <li>All layers will be permanently deleted</li>
          <li>All viewports will be permanently deleted</li>
          <li>All layer elements will be permanently deleted</li>
        </ul>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-2">
        <Button
          label="Cancel"
          severity="secondary"
          outlined
          @click="handleClose"
          :disabled="loading"
        />
        <Button
          label="Delete Map"
          severity="danger"
          @click="handleConfirm"
          :loading="loading"
          icon="pi pi-trash"
        />
      </div>
    </template>
  </Dialog>
</template>
