<script setup>
import { computed, ref } from 'vue';
import AlertsMenu from './AlertsMenu.vue';

const activeTab = ref('active');
const sortKey = ref('time');
const searchQuery = ref('');

const alerts = [
  {
    id: 1,
    code: 'OD3 Intake',
    location: 'Main Vestibule',
    time: '09:51 AM',
    timestamp: 1734786660,
    status: 'active',
    severity: 'critical',
    summary: 'Door forced open from intake hallway'
  },
  {
    id: 2,
    code: 'OD8 UnitB',
    location: 'Unit B Corridor',
    time: '09:49 AM',
    timestamp: 1734786540,
    status: 'processed',
    severity: 'warning',
    summary: 'Camera offline longer than threshold'
  },
  {
    id: 3,
    code: 'OD3 UnitA',
    location: 'North Gate',
    time: '08:48 AM',
    timestamp: 1734781680,
    status: 'active',
    severity: 'warning',
    summary: 'Badge reader queue exceeded limit'
  },
  {
    id: 4,
    code: 'A1000',
    location: 'Outside Door',
    time: '07:25 AM',
    timestamp: 1734775500,
    status: 'archived',
    severity: 'info',
    summary: 'Sensor test completed successfully'
  },
  {
    id: 5,
    code: 'OD6 UnitA',
    location: 'North Gate',
    time: '08:58 AM',
    timestamp: 1734781680,
    status: 'active',
    severity: 'warning',
    summary: 'Sensor triggered by John Doe'
  },
  {
    id: 6,
    code: 'OD7 UnitA',
    location: 'South Gate',
    time: '09:58 AM',
    timestamp: 1734781680,
    status: 'active',
    severity: 'warning',
    summary: 'Sensor triggered by Jane Doe'
  }
];

const severityMeta = {
  critical: {
    pill: 'bg-gradient-to-r from-red-500 to-orange-500 text-white',
    dot: 'bg-red-500'
  },
  warning: {
    pill: 'bg-orange-100 text-orange-600 dark:bg-orange-500/20 dark:text-orange-300',
    dot: 'bg-orange-500'
  },
  info: {
    pill: 'bg-surface-100 text-surface-500 dark:bg-surface-800 dark:text-surface-300',
    dot: 'bg-surface-400'
  }
};

const severityOrder = {
  critical: 3,
  warning: 2,
  info: 1
};

const statusFilters = {
  active: ['active'],
  processed: ['processed'],
  archived: ['archived']
};

const severityCardTone = {
  critical: {
    border: 'border-red-200 dark:border-red-500/40',
    collapsedBg: 'bg-gradient-to-r from-red-600/90 to-orange-500/80',
    collapsedText: 'text-white'
  },
  warning: {
    border: 'border-orange-200 dark:border-orange-400/40',
    collapsedBg: 'bg-orange-50 dark:bg-orange-500/15',
    collapsedText: 'text-orange-700 dark:text-orange-200'
  },
  info: {
    border: 'border-surface-200 dark:border-surface-600',
    collapsedBg: 'bg-surface-100 dark:bg-surface-800',
    collapsedText: 'text-surface-700 dark:text-surface-200'
  }
};

const sortComparators = {
  severity: (a, b) => severityOrder[b.severity] - severityOrder[a.severity],
  time: (a, b) => b.timestamp - a.timestamp,
  location: (a, b) => a.location.localeCompare(b.location)
};

const filteredAlerts = computed(() => {
  const normalizedQuery = searchQuery.value.toLowerCase().trim();
  const allowedStatuses = statusFilters[activeTab.value] ?? [];

  const matches = alerts.filter((alert) => {
    const statusMatch = allowedStatuses.includes(alert.status);
    const textMatch = !normalizedQuery
      || alert.code.toLowerCase().includes(normalizedQuery)
      || alert.location.toLowerCase().includes(normalizedQuery)
      || alert.summary.toLowerCase().includes(normalizedQuery);

    return statusMatch && textMatch;
  });

  const sorter = sortComparators[sortKey.value];
  return sorter ? [...matches].sort(sorter) : matches;
});

const handleTab = (value) => {
  activeTab.value = value;
};

const handleSort = (value) => {
  sortKey.value = value;
};

const expandedAlerts = ref(new Set(alerts.map((alert) => alert.id)));

const isExpanded = (id) => expandedAlerts.value.has(id);

const toggleCard = (id) => {
  const next = new Set(expandedAlerts.value);
  if (next.has(id)) {
    next.delete(id);
  } else {
    next.add(id);
  }
  expandedAlerts.value = next;
};
</script>

<template>
  <aside class="flex h-full min-h-0 max-h-full flex-col overflow-hidden rounded-3xl border border-surface-200 bg-surface-0 shadow-sm dark:border-surface-700 dark:bg-surface-900">
    <header class="flex flex-shrink-0 items-center justify-between border-b border-surface-200 px-4 py-4 dark:border-surface-800">
      <div>
        <p class="text-xs uppercase tracking-[0.3em] text-primary">Alerts</p>
        <h2 class="text-xl font-semibold text-surface-900 dark:text-surface-0">Main Control Queue</h2>
      </div>
      <button type="button" class="rounded-full border border-surface-200 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-surface-500 hover:text-primary dark:border-surface-800">
        Filter
      </button>
    </header>

    <AlertsMenu
      class="flex-shrink-0"
      :active-tab="activeTab"
      :sort-key="sortKey"
      @update:activeTab="handleTab"
      @update:sortKey="handleSort"
    />

    <div class="flex-shrink-0 px-4 pb-4">
      <label class="flex items-center gap-2 rounded-full border border-surface-200 bg-surface-0 px-4 py-2 text-sm text-surface-500 dark:border-surface-700 dark:bg-surface-800">
        <span class="pi pi-search text-xs" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search alerts"
          class="flex-1 bg-transparent text-sm text-surface-900 outline-none dark:text-surface-0"
        >
      </label>
    </div>

    <div class="flex-1 min-h-0 overflow-y-auto px-4 pb-6">
      <div class="flex flex-col gap-4">
        <article
          v-for="alert in filteredAlerts"
          :key="alert.id"
          class="rounded-2xl border p-4 shadow-sm transition hover:shadow-md"
          :class="[
            severityCardTone[alert.severity].border,
            !isExpanded(alert.id) ? `${severityCardTone[alert.severity].collapsedBg} ${severityCardTone[alert.severity].collapsedText}` : 'bg-surface-0 text-surface-900 dark:bg-surface-900 dark:text-surface-0'
          ]"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-2">
              <button
                type="button"
                class="flex h-9 w-9 items-center justify-center rounded-full border border-surface-200 text-surface-500 transition hover:border-primary hover:text-primary dark:border-surface-600"
                aria-label="View in map"
              >
                <span class="pi pi-map-marker text-base" />
              </button>
              <button
                type="button"
                class="flex h-9 w-9 items-center justify-center rounded-full border border-surface-200 text-surface-500 transition hover:border-primary hover:text-primary dark:border-surface-600"
                :aria-expanded="isExpanded(alert.id).toString()"
                :aria-controls="`alert-card-${alert.id}`"
                @click="toggleCard(alert.id)"
              >
                <span :class="['pi text-base', isExpanded(alert.id) ? 'pi-chevron-up' : 'pi-chevron-down']" />
              </button>
            </div>
            <span class="text-xs font-semibold uppercase tracking-[0.4em]" :class="!isExpanded(alert.id) ? 'text-current' : 'text-surface-500 dark:text-surface-400'">
              {{ alert.time }}
            </span>
          </div>

          <div :id="`alert-card-${alert.id}`" class="mt-4">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-xs uppercase tracking-[0.5em]" :class="!isExpanded(alert.id) ? 'text-current opacity-80' : 'text-surface-500'">
                  {{ alert.code }}
                </p>
                <h3 class="text-lg font-semibold" :class="!isExpanded(alert.id) ? 'text-current' : 'text-surface-900 dark:text-surface-0'">
                  {{ alert.location }}
                </h3>
              </div>
              <span
                v-if="isExpanded(alert.id)"
                class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold"
                :class="severityMeta[alert.severity].pill"
              >
                <span class="h-2 w-2 rounded-full" :class="severityMeta[alert.severity].dot" />
                {{ alert.severity.toUpperCase() }}
              </span>
            </div>

            <template v-if="isExpanded(alert.id)">
              <p class="mt-3 text-sm text-surface-600 dark:text-surface-300">{{ alert.summary }}</p>

              <div class="mt-4 grid grid-cols-2 gap-2 text-xs font-semibold uppercase tracking-wider">
                <button type="button" class="rounded-full border border-surface-200 px-3 py-2 text-surface-600 hover:text-primary dark:border-surface-700">Mark Processed</button>
                <button type="button" class="rounded-full border border-red-200 px-3 py-2 text-red-600 hover:bg-red-50">Eliminate Alarm</button>
              </div>
            </template>
            <p
              v-else
              class="mt-3 text-xs font-semibold uppercase tracking-[0.3em]"
            >
              {{ alert.severity }} alert
            </p>
          </div>
        </article>
      </div>
    </div>
  </aside>
</template>
