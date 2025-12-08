<script setup>
import IcasLayout from '@/Layouts/IcasLayout.vue';
</script>

<template>
  <IcasLayout title="Dashboard">
    <div class="space-y-6">
      <!-- Page Title -->
      <h1 class="text-3xl font-bold text-surface-900 dark:text-surface-0">Dashboard</h1>

      <!-- Statistics Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Active Sensors Card -->
        <div class="bg-surface-0 dark:bg-surface-800 p-6 rounded-lg border border-surface-200 dark:border-surface-700 shadow-sm">
          <div class="flex flex-col gap-3">
            <div class="text-surface-600 dark:text-surface-400 font-medium">Active Sensors</div>
            <div class="text-4xl font-bold text-surface-900 dark:text-surface-0">24</div>
          </div>
        </div>

        <!-- Active Alerts Card -->
        <div class="bg-surface-0 dark:bg-surface-800 p-6 rounded-lg border border-surface-200 dark:border-surface-700 shadow-sm">
          <div class="flex flex-col gap-3">
            <div class="text-surface-600 dark:text-surface-400 font-medium">Active Alerts</div>
            <div class="text-4xl font-bold text-red-600">3</div>
          </div>
        </div>

        <!-- System Status Card -->
        <div class="bg-surface-0 dark:bg-surface-800 p-6 rounded-lg border border-surface-200 dark:border-surface-700 shadow-sm">
          <div class="flex flex-col gap-3">
            <div class="text-surface-600 dark:text-surface-400 font-medium">System Status</div>
            <div class="text-2xl font-bold text-green-600">Online</div>
          </div>
        </div>

        <!-- Connected Devices Card -->
        <div class="bg-surface-0 dark:bg-surface-800 p-6 rounded-lg border border-surface-200 dark:border-surface-700 shadow-sm">
          <div class="flex flex-col gap-3">
            <div class="text-surface-600 dark:text-surface-400 font-medium">Connected Devices</div>
            <div class="text-4xl font-bold text-surface-900 dark:text-surface-0">32</div>
          </div>
        </div>
      </div>
    </div>
  </IcasLayout>
</template>
