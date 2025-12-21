<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
  activeTab: {
    type: String,
    default: 'active'
  },
  sortKey: {
    type: String,
    default: 'time'
  }
});

const emit = defineEmits(['update:activeTab', 'update:sortKey']);

const currentTab = ref(props.activeTab);

const tabOptions = [
  { key: 'active', label: 'Active' },
  { key: 'processed', label: 'Processed' },
  { key: 'archived', label: 'Archived' }
];

const sortOptions = [
  { key: 'severity', label: 'Severity' },
  { key: 'time', label: 'Time' },
  { key: 'location', label: 'Location' }
];

const scrollContainer = ref(null);
const tabRefs = ref([]);
const indicatorStyle = ref({ width: '0px', transform: 'translateX(0px)' });

const activeIndex = computed(() => tabOptions.findIndex((tab) => tab.key === currentTab.value));

const setTabRef = (el, index) => {
  if (el) {
    tabRefs.value[index] = el;
  }
};

const updateIndicator = async () => {
  await nextTick();
  const index = activeIndex.value;
  const button = index >= 0 ? tabRefs.value[index] : null;
  if (!button) {
    return;
  }

  indicatorStyle.value = {
    width: `${button.offsetWidth}px`,
    transform: `translateX(${button.offsetLeft}px)`
  };
};

const scrollActiveIntoView = async (index) => {
  await nextTick();
  const container = scrollContainer.value;
  const button = index >= 0 ? tabRefs.value[index] : null;
  if (!container || !button) {
    return;
  }

  if (button.offsetLeft < container.scrollLeft) {
    container.scrollTo({ left: button.offsetLeft, behavior: 'smooth' });
    return;
  }

  const rightEdge = container.scrollLeft + container.offsetWidth;
  const buttonRight = button.offsetLeft + button.offsetWidth;
  if (buttonRight > rightEdge) {
    container.scrollTo({ left: buttonRight - container.offsetWidth, behavior: 'smooth' });
  }
};

const setActiveTab = (key) => {
  if (currentTab.value === key) {
    return;
  }
  currentTab.value = key;
  emit('update:activeTab', key);
  scrollActiveIntoView(activeIndex.value);
};

const handleSort = (event) => emit('update:sortKey', event.target.value);
watch(() => props.activeTab, (value) => {
  currentTab.value = value;
});

watch(currentTab, () => {
  updateIndicator();
  scrollActiveIntoView(activeIndex.value);
});

onMounted(() => {
  updateIndicator();
  window.addEventListener('resize', updateIndicator);
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', updateIndicator);
});
</script>

<template>
  <div class="flex flex-col gap-3 p-4">
    <div class="relative overflow-hidden rounded-2xl border border-surface-200 dark:border-surface-700">
      <div ref="scrollContainer" class="overflow-x-auto">
        <ul class="cursor-pointer px-6 py-3 flex items-center gap-2 transition-colors duration-150 hover:text-primary">
          <template v-for="(tab, index) in tabOptions" :key="tab.key">
            <li class="flex">
              <a
                class="px-6 py-3 text-sm font-medium transition-colors"
                :class="
                  tab.key === currentTab
                    ? 'text-primary dark:border-primary font-semibold'
                    : 'text-surface-400 hover:text-surface-600 dark:text-surface-500'
                "
                :ref="(el) => setTabRef(el, index)"
                @click="setActiveTab(tab.key)"
              >
                <span>{{ tab.label }}</span>
            </a>
            </li>
          </template>
          <div class="pointer-events-none absolute bottom-0 h-[2px] bg-primary transition-[width,transform] duration-300 ease-out z-20" :style="indicatorStyle" />
        </ul>
      </div>
    </div>
    <div class="flex items-center justify-between text-xs uppercase tracking-[0.3em] text-surface-500">
      <span>Filter</span>
      <label class="flex items-center gap-2 text-[0.65rem] normal-case tracking-normal text-surface-500">
        Sort by
        <select class="app-select rounded-full text-xs" :value="sortKey" @change="handleSort">
          <option v-for="option in sortOptions" :key="option.key" :value="option.key">
            {{ option.label }}
          </option>
        </select>
      </label>
    </div>
  </div>
</template>
