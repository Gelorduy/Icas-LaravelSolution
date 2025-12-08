<script setup>
import IcasLayout from '@/Layouts/IcasLayout.vue';
</script>

<template>
  <IcasLayout title="Map Visualization">
    <div class="space-y-6">
      <h1 class="text-3xl font-bold text-surface-900 dark:text-surface-0">Map Visualization</h1>
      <div class="border-2 border-dashed border-surface-200 dark:border-surface-700 rounded-2xl bg-surface-50 dark:bg-surface-800 p-12 flex items-center justify-center min-h-96">
        <div class="text-center">
          <i class="pi pi-map text-6xl text-surface-400 mb-4"></i>
          <p class="text-surface-500 text-lg font-semibold">Map Canvas Area</p>
        </div>
      </div>
    </div>
  </IcasLayout>
</template>
