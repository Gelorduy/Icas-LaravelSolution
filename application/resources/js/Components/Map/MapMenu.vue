<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  activeKey: {
    type: String,
    default: null
  },
  orientation: {
    type: String,
    default: 'horizontal',
    validator: (value) => ['horizontal', 'vertical'].includes(value)
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'overlay'].includes(value)
  }
});

const emit = defineEmits(['update:activeKey']);

const page = usePage();

const allMenuTabs = [
  { key: 'actions', label: 'Actions' },
  { key: 'edit', label: 'Edit' },
  { key: 'options', label: 'Options' },
  { key: 'tools', label: 'Tools' },
  { key: 'layers', label: 'Layers' },
  { key: 'viewports', label: 'ViewPorts' },
  { key: 'maps', label: 'Maps' },
  { key: 'import', label: 'Import Map' }
];

const allowedMenuItems = computed(() => page.props.permissions?.allowedMapMenuItems || []);

const menuTabs = computed(() => {
  return allMenuTabs.filter(tab => allowedMenuItems.value.includes(tab.key));
});

const activeTab = computed(() => props.activeKey ?? menuTabs.value[0]?.key);

const handleSelect = (key) => {
  emit('update:activeKey', key);
};

const navClasses = computed(() => {
  if (props.orientation === 'vertical') {
    return 'flex flex-col gap-2 rounded-2xl border border-surface-200 bg-surface-0/90 p-4 shadow-xl backdrop-blur dark:border-surface-700 dark:bg-surface-900/90';
  }

  if (props.variant === 'overlay') {
    return 'flex flex-wrap justify-end gap-2 rounded-full border border-surface-200 bg-surface-0/90 px-4 py-2 shadow-xl backdrop-blur dark:border-surface-700 dark:bg-surface-900/90';
  }

  return 'flex flex-wrap gap-2 rounded-t-2xl bg-primary-50/50 p-4 dark:bg-primary-900/20';
});

const buttonClasses = computed(() => {
  if (props.orientation === 'vertical') {
    return 'w-full px-4 py-2 text-left';
  }
  if (props.variant === 'overlay') {
    return 'px-3 py-1.5';
  }
  return 'px-4 py-2';
});
</script>

<template>
  <nav :class="navClasses">
    <button
      v-for="tab in menuTabs"
      :key="tab.key"
      type="button"
      class="rounded-full text-sm font-medium transition-colors"
      :class="[
        tab.key === activeTab ? 'bg-primary text-primary-contrast shadow' : 'bg-surface-0 dark:bg-surface-800 text-surface-500 hover:text-primary',
        buttonClasses
      ]"
      @click="handleSelect(tab.key)"
    >
      {{ tab.label }}
    </button>
  </nav>
</template>
