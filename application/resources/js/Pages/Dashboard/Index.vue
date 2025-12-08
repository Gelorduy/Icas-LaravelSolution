<script setup>
import IcasLayout from '@/Layouts/IcasLayout.vue';
</script>

<template>
  <IcasLayout title="Dashboard">
    <div class="space-y-6">
      <h1 class="text-3xl font-bold text-surface-900 dark:text-surface-0">Dashboard</h1>
      
      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-surface-50 dark:bg-surface-800 p-6 rounded-lg border border-surface-200 dark:border-surface-700">
          <p class="text-surface-600 dark:text-surface-300 text-sm font-medium">Active Sensors</p>
          <p class="text-3xl font-bold text-surface-900 dark:text-surface-0 mt-2">24</p>
        </div>
        <div class="bg-surface-50 dark:bg-surface-800 p-6 rounded-lg border border-surface-200 dark:border-surface-700">
          <p class="text-surface-600 dark:text-surface-300 text-sm font-medium">Active Alerts</p>
          <p class="text-3xl font-bold text-surface-900 dark:text-surface-0 mt-2">3</p>
        </div>
        <div class="bg-surface-50 dark:bg-surface-800 p-6 rounded-lg border border-surface-200 dark:border-surface-700">
          <p class="text-surface-600 dark:text-surface-300 text-sm font-medium">System Status</p>
          <p class="text-3xl font-bold text-green-600 mt-2">Online</p>
        </div>
        <div class="bg-surface-50 dark:bg-surface-800 p-6 rounded-lg border border-surface-200 dark:border-surface-700">
          <p class="text-surface-600 dark:text-surface-300 text-sm font-medium">Connected Devices</p>
          <p class="text-3xl font-bold text-surface-900 dark:text-surface-0 mt-2">32</p>
        </div>
      </div>
    </div>
  </IcasLayout>
</template>
