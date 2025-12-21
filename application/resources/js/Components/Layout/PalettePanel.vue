<script setup>
import { computed, onMounted, ref } from 'vue';
import { PRIMARY_PALETTES, SURFACE_PALETTES, STORAGE_KEYS, applyPalette } from '@/utils/themePalettes';

const emit = defineEmits(['close']);

const panelRef = ref(null);
const selectedPrimary = ref(PRIMARY_PALETTES[0].key);
const selectedSurface = ref(SURFACE_PALETTES[0].key);

onMounted(() => {
  if (typeof window !== 'undefined') {
    selectedPrimary.value = window.localStorage?.getItem(STORAGE_KEYS.primary) ?? PRIMARY_PALETTES[0].key;
    selectedSurface.value = window.localStorage?.getItem(STORAGE_KEYS.surface) ?? SURFACE_PALETTES[0].key;
  }

  requestAnimationFrame(() => {
    panelRef.value?.focus();
  });
});

const primaryLabel = computed(() => PRIMARY_PALETTES.find((item) => item.key === selectedPrimary.value)?.label ?? '');
const surfaceLabel = computed(() => SURFACE_PALETTES.find((item) => item.key === selectedSurface.value)?.label ?? '');

const gradientStyle = (palette) => ({
  background: `linear-gradient(135deg, ${palette.preview[0]}, ${palette.preview[1]})`,
});

const selectPrimary = (key) => {
  const applied = applyPalette('primary', key);
  if (applied) {
    selectedPrimary.value = applied;
  }
};

const selectSurface = (key) => {
  const applied = applyPalette('surface', key);
  if (applied) {
    selectedSurface.value = applied;
  }
};

const resetTheme = () => {
  selectPrimary(PRIMARY_PALETTES[0].key);
  selectSurface(SURFACE_PALETTES[0].key);
};

const closePanel = () => {
  emit('close');
};
</script>

<template>
  <div
    ref="panelRef"
    tabindex="-1"
    class="absolute right-0 top-11 w-[22rem] rounded-3xl border border-surface-200 bg-surface-0 p-5 text-left shadow-2xl backdrop-blur dark:border-surface-700 dark:bg-surface-900 z-50"
    @click.stop
    @keydown.esc.prevent.stop="closePanel"
  >
    <header class="flex items-center justify-between">
      <div>
        <p class="text-[0.55rem] font-semibold uppercase tracking-[0.4em] text-surface-500">Theme Studio</p>
        <p class="text-sm text-surface-600 dark:text-surface-300">Quickly preview palette changes</p>
      </div>
      <button
        type="button"
        class="flex h-8 w-8 items-center justify-center rounded-full border border-surface-200 text-surface-500 transition hover:border-primary hover:text-primary dark:border-surface-700"
        @click.stop="closePanel"
      >
        <i class="pi pi-times text-sm" />
      </button>
    </header>

    <section class="mt-5">
      <div class="flex items-center justify-between">
        <span class="text-[0.6rem] font-semibold uppercase tracking-[0.4em] text-surface-500">Primary Accent</span>
        <span class="text-[0.6rem] font-medium text-surface-400">{{ primaryLabel }}</span>
      </div>
      <div class="mt-3 grid grid-cols-5 gap-2">
        <button
          v-for="palette in PRIMARY_PALETTES"
          :key="palette.key"
          type="button"
          class="group flex flex-col items-center gap-2 rounded-2xl p-2 transition"
          :class="selectedPrimary === palette.key ? 'bg-surface-50 ring-1 ring-primary/50 dark:bg-surface-800' : 'hover:bg-surface-50 hover:ring-1 hover:ring-surface-200 dark:hover:bg-surface-800'"
          @click.stop="selectPrimary(palette.key)"
        >
          <span class="relative block h-11 w-full rounded-2xl" :style="gradientStyle(palette)">
            <span
              v-if="selectedPrimary === palette.key"
              class="absolute inset-0 flex items-center justify-center text-sm font-semibold text-white drop-shadow"
            >
              <i class="pi pi-check" />
            </span>
          </span>
          <span class="text-[0.55rem] font-semibold uppercase tracking-[0.3em] text-surface-500">{{ palette.shortLabel }}</span>
        </button>
      </div>
    </section>

    <section class="mt-6">
      <div class="flex items-center justify-between">
        <span class="text-[0.6rem] font-semibold uppercase tracking-[0.4em] text-surface-500">Surface Base</span>
        <span class="text-[0.6rem] font-medium text-surface-400">{{ surfaceLabel }}</span>
      </div>
      <div class="mt-3 grid grid-cols-3 gap-3">
        <button
          v-for="palette in SURFACE_PALETTES"
          :key="palette.key"
          type="button"
          class="group flex flex-1 flex-col items-center gap-2 rounded-2xl border p-3 transition"
          :class="selectedSurface === palette.key ? 'border-primary text-primary' : 'border-surface-200 hover:border-primary/60 dark:border-surface-700'"
          @click.stop="selectSurface(palette.key)"
        >
          <span class="relative flex h-12 w-full items-center justify-center rounded-xl border border-dashed border-surface-200 bg-gradient-to-br from-white to-surface-100 dark:border-surface-700" :style="gradientStyle(palette)">
            <span
              v-if="selectedSurface === palette.key"
              class="absolute inset-0 flex items-center justify-center text-base font-semibold text-white drop-shadow"
            >
              <i class="pi pi-check" />
            </span>
          </span>
          <span class="text-[0.6rem] font-semibold uppercase tracking-[0.3em] text-surface-500">{{ palette.shortLabel }}</span>
        </button>
      </div>
    </section>

    <footer class="mt-6 flex items-center justify-between">
      <button
        type="button"
        class="text-[0.65rem] font-semibold uppercase tracking-[0.4em] text-primary hover:text-primary-400"
        @click.stop="resetTheme"
      >
        Reset
      </button>
      <button
        type="button"
        class="text-[0.65rem] font-semibold uppercase tracking-[0.4em] text-surface-500 hover:text-surface-700"
        @click.stop="closePanel"
      >
        Close
      </button>
    </footer>
  </div>
</template>
