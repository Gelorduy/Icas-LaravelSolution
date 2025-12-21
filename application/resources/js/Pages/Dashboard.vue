<script setup>
import IcasLayout from '@/Layouts/IcasLayout.vue';
</script>

<template>
  <IcasLayout title="Dashboard">
    <div class="space-y-6">
      <!-- Page Title -->
      <h1 class="text-3xl font-bold text-surface-900 dark:text-surface-0">Dashboard</h1>

      <!-- Statistics Cards Grid -->
      <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
        <!-- Active Sensors Card -->
        <div class="rounded-lg border border-surface-200 bg-surface-0 p-6 shadow-sm dark:border-surface-700 dark:bg-surface-800">
          <div class="flex flex-col gap-3">
            <div class="font-medium text-surface-600 dark:text-surface-400">Active Sensors</div>
            <div class="text-4xl font-bold text-surface-900 dark:text-surface-0">24</div>
          </div>
        </div>

        <!-- Active Alerts Card -->
        <div class="rounded-lg border border-surface-200 bg-surface-0 p-6 shadow-sm dark:border-surface-700 dark:bg-surface-800">
          <div class="flex flex-col gap-3">
            <div class="font-medium text-surface-600 dark:text-surface-400">Active Alerts</div>
            <div class="text-4xl font-bold text-red-600">3</div>
          </div>
        </div>

        <!-- System Status Card -->
        <div class="rounded-lg border border-surface-200 bg-surface-0 p-6 shadow-sm dark:border-surface-700 dark:bg-surface-800">
          <div class="flex flex-col gap-3">
            <div class="font-medium text-surface-600 dark:text-surface-400">System Status</div>
            <div class="text-2xl font-bold text-green-600">Online</div>
          </div>
        </div>

        <!-- Connected Devices Card -->
        <div class="rounded-lg border border-surface-200 bg-surface-0 p-6 shadow-sm dark:border-surface-700 dark:bg-surface-800">
          <div class="flex flex-col gap-3">
            <div class="font-medium text-surface-600 dark:text-surface-400">Connected Devices</div>
            <div class="text-4xl font-bold text-surface-900 dark:text-surface-0">32</div>
          </div>
        </div>
      </div>
    </div>
  </IcasLayout>
</template>
