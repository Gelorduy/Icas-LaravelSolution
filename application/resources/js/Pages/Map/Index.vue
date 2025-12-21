<script setup>
import MapWorkspace from '@/Components/Map/MapWorkspace.vue';
import AlertsPanel from '@/Components/Alerts/AlertsPanel.vue';
import SensorsDock from '@/Components/Sensors/SensorsDock.vue';
import IcasLayout from '@/Layouts/IcasLayout.vue';
import Splitter from 'primevue/splitter';
import SplitterPanel from 'primevue/splitterpanel';
</script>

<template>
  <IcasLayout title="Map Visualization">
    <Splitter
      layout="horizontal"
      class="rounded-3xl border border-surface-200 bg-surface-0 shadow-sm dark:border-surface-700 dark:bg-surface-900"
      style="height: calc(100vh - 8rem)"
    >
      <SplitterPanel :size="70" :minSize="40" class="flex min-h-0 min-w-0 flex-col gap-4 overflow-hidden p-4">
        <Splitter layout="vertical" class="flex-1 min-h-0" style="height: 100%">
          <SplitterPanel :size="65" :minSize="40" class="flex min-h-0 min-w-0 flex-col overflow-hidden">
            <MapWorkspace class="h-full" />
          </SplitterPanel>
          <SplitterPanel :size="35" :minSize="20" class="flex min-h-0 min-w-0 flex-col overflow-hidden">
            <SensorsDock class="h-full" />
          </SplitterPanel>
        </Splitter>
      </SplitterPanel>
      <SplitterPanel :size="30" :minSize="20" class="flex min-h-0 min-w-0 flex-col overflow-hidden p-4">
        <AlertsPanel class="h-full" />
      </SplitterPanel>
    </Splitter>
  </IcasLayout>
</template>
