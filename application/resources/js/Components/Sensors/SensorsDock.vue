<script setup>
import { computed, ref } from 'vue';
import SensorsMenu from './SensorsMenu.vue';

const viewMode = ref('grid');

const sensors = [
  { id: 'A1000', name: 'Door F Outside', status: 'alert', depth: 'North Wing' },
  { id: 'A2058', name: 'Secure Vestibule Outer', status: 'warning', depth: 'Control' },
  { id: 'A2058B', name: 'Secure Vestibule Inner', status: 'online', depth: 'Control' },
  { id: 'C1200', name: 'Camera Pod C1', status: 'offline', depth: 'Pod C' },
  { id: 'S550', name: 'Smoke Loop East', status: 'online', depth: 'East Hall' },
  { id: 'L930', name: 'Lock Cluster Intake', status: 'warning', depth: 'Intake' }
];

const statusTone = {
  alert: {
    border: 'border-red-300 dark:border-red-500/60',
    text: 'text-red-600 dark:text-red-300',
    badge: 'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-200'
  },
  warning: {
    border: 'border-orange-300 dark:border-orange-400/60',
    text: 'text-orange-600 dark:text-orange-200',
    badge: 'bg-orange-50 text-orange-600 dark:bg-orange-400/20 dark:text-orange-100'
  },
  offline: {
    border: 'border-surface-300 dark:border-surface-600',
    text: 'text-surface-500 dark:text-surface-400',
    badge: 'bg-surface-200 text-surface-600 dark:bg-surface-700 dark:text-surface-300'
  },
  online: {
    border: 'border-green-300 dark:border-green-500/60',
    text: 'text-green-600 dark:text-green-300',
    badge: 'bg-green-50 text-green-600 dark:bg-green-500/20 dark:text-green-200'
  }
};

const cardLayout = computed(() => viewMode.value === 'grid'
  ? 'grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3'
  : 'space-y-3'
);
</script>

<template>
  <section class="flex h-full min-h-0 flex-col rounded-3xl border border-surface-200 bg-surface-0 p-4 shadow-sm dark:border-surface-700 dark:bg-surface-900">
    <SensorsMenu :view="viewMode" @update:view="viewMode = $event" />

    <div class="mt-4 flex-1 min-h-0 overflow-auto">
      <div class="pb-2" :class="cardLayout">
        <article
          v-for="sensor in sensors"
          :key="sensor.id"
          class="rounded-2xl border bg-surface-0 p-4 transition hover:-translate-y-0.5 hover:shadow-md dark:bg-surface-900"
          :class="statusTone[sensor.status].border"
        >
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs uppercase tracking-[0.4em] text-surface-500">{{ sensor.id }}</p>
              <h3 class="text-base font-semibold text-surface-900 dark:text-surface-0">{{ sensor.name }}</h3>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="statusTone[sensor.status].badge">
              {{ sensor.status.toUpperCase() }}
            </span>
          </div>
          <p class="mt-2 text-sm text-surface-500 dark:text-surface-300">{{ sensor.depth }}</p>
          <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold uppercase tracking-[0.3em]" :class="statusTone[sensor.status].text">
            <span class="rounded-full border border-current px-3 py-1">Details</span>
            <span class="rounded-full border border-current px-3 py-1">Map</span>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>
