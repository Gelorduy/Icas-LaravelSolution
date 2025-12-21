<script setup>
const props = defineProps({
  view: {
    type: String,
    default: 'grid'
  }
});

const emit = defineEmits(['update:view']);

const viewOptions = [
  { key: 'grid', icon: 'pi pi-th-large', label: 'Grid' },
  { key: 'list', icon: 'pi pi-bars', label: 'List' }
];

const handleView = (key) => emit('update:view', key);
</script>

<template>
  <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-surface-200 bg-surface-0 px-4 py-3 text-xs uppercase tracking-[0.3em] text-surface-500 dark:border-surface-700 dark:bg-surface-900">
    <span>Sensors</span>
    <div class="flex items-center gap-2">
      <button
        v-for="option in viewOptions"
        :key="option.key"
        type="button"
        class="flex items-center gap-2 rounded-full border px-3 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.25em]"
        :class="option.key === view ? 'border-primary text-primary' : 'border-transparent text-surface-400 bg-surface-100 dark:bg-surface-800'"
        @click="handleView(option.key)"
      >
        <span :class="option.icon" />
        {{ option.label }}
      </button>
      <button type="button" class="rounded-full border border-surface-200 px-3 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.25em] text-surface-500 hover:text-primary dark:border-surface-700">
        Filter
      </button>
    </div>
  </div>
</template>
