<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useMapStore } from '@/Stores/mapStore';

const mapStore = useMapStore();
const svgRef = ref(null);
const viewBox = ref('0 0 1000 1000');

const activeMap = computed(() => mapStore.manifest?.map);
const activeViewport = computed(() => mapStore.activeViewport);

const normalizeBounds = () => {
  const base = mapStore.zoomLevel.base;
  const bounds = activeViewport.value?.bounds;
  if (!bounds) {
    return { x: 0, y: 0, width: base.width, height: base.height };
  }

  const usesRelative = bounds.mode === 'relative' || bounds.relative === true || ((bounds.width ?? 0) <= 1 && (bounds.height ?? 0) <= 1);
  const toValue = (value, axis) => {
    if (usesRelative) {
      return Number(value ?? 0) * (axis === 'x' ? base.width : base.height);
    }
    return Number(value ?? 0);
  };

  return {
    x: toValue(bounds.x ?? 0, 'x'),
    y: toValue(bounds.y ?? 0, 'y'),
    width: toValue(bounds.width ?? base.width, 'x'),
    height: toValue(bounds.height ?? base.height, 'y'),
  };
};

const applyViewportToViewBox = () => {
  const { x, y, width, height } = normalizeBounds();
  const scale = mapStore.zoomLevel.scale ?? 1;
  const adjustedWidth = width / scale;
  const adjustedHeight = height / scale;
  viewBox.value = `${x} ${y} ${adjustedWidth} ${adjustedHeight}`;
};

const handleWheel = (event) => {
  if (!mapStore.manifest) return;
  const direction = event.deltaY > 0 ? -1 : 1;
  const step = 0.1 * direction;
  const nextZoom = Math.max(0.1, (mapStore.zoom ?? 1) + step);
  mapStore.setZoom(nextZoom);
};

const getLayerOpacity = (layer) => layer.style_preset?.opacity ?? 1;
const getOverlayPath = (layer) => layer.data_source?.svg_path ?? layer.data_source?.url;
const getGeoFeatures = (layer) => layer.data_source?.features ?? [];
const getDevices = (layer) => layer.data_source?.devices ?? [];
const deviceFill = (device) => (device.status === 'critical' ? '#ef4444' : '#38bdf8');
const deviceRadius = (device) => (device.status === 'critical' ? 12 : 8);

onMounted(() => {
  applyViewportToViewBox();
});

watch(activeViewport, applyViewportToViewBox);
watch(() => mapStore.zoomLevel, applyViewportToViewBox, { deep: true });
</script>

<template>
  <div class="relative flex h-full flex-1 select-none flex-col overflow-hidden rounded-3xl border border-surface-200 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 shadow-2xl dark:border-surface-700">
    <header class="flex items-center justify-between border-b border-white/10 bg-white/5 px-4 py-3 text-sm text-white">
      <div class="flex items-center gap-3">
        <span class="text-xs uppercase tracking-[0.4em] text-white/70">Map Canvas</span>
        <div class="h-1 w-28 rounded-full bg-white/10">
          <div class="h-1 rounded-full bg-primary" :style="{ width: `${Math.min((mapStore.zoom ?? 1) * 50, 100)}%` }"></div>
        </div>
      </div>
      <div class="text-xs text-white/70">
        {{ activeMap?.name || 'No map loaded' }}
      </div>
    </header>
    <div
      class="relative flex-1 overflow-hidden"
      @wheel.prevent="handleWheel"
    >
      <svg
        ref="svgRef"
        :viewBox="viewBox"
        class="h-full w-full bg-slate-950/80"
        preserveAspectRatio="xMidYMid meet"
      >
        <image
          v-if="activeMap?.svg_asset_path"
          :href="activeMap.svg_asset_path"
          x="0"
          y="0"
          :width="mapStore.zoomLevel.base.width"
          :height="mapStore.zoomLevel.base.height"
          opacity="0.9"
        />

        <g v-for="layer in mapStore.visibleLayers" :key="layer.id" :opacity="getLayerOpacity(layer)">
          <template v-if="layer.layer_type === 'svg_overlay'">
            <image
              v-if="getOverlayPath(layer)"
              :href="getOverlayPath(layer)"
              x="0"
              y="0"
              :width="mapStore.zoomLevel.base.width"
              :height="mapStore.zoomLevel.base.height"
              :opacity="layer.style_preset?.opacity ?? 0.8"
            />
          </template>

          <template v-else-if="layer.layer_type === 'geojson'">
            <g
              :fill="layer.style_preset?.fill || 'rgba(255,255,255,0.1)'"
              :stroke="layer.style_preset?.stroke || '#00E0FF'"
              :stroke-width="layer.style_preset?.strokeWidth || 2"
            >
              <polygon
                v-for="feature in getGeoFeatures(layer)"
                :key="feature.id"
                :points="feature.points"
              />
            </g>
          </template>

          <template v-else-if="layer.layer_type === 'device_cluster'">
            <g>
              <circle
                v-for="device in getDevices(layer)"
                :key="device.id"
                :cx="device.x"
                :cy="device.y"
                :r="deviceRadius(device)"
                :fill="deviceFill(device)"
                :opacity="0.85"
              />
            </g>
          </template>

          <template v-else>
            <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle" fill="white" opacity="0.5">
              Unknown layer type: {{ layer.layer_type }}
            </text>
          </template>
        </g>
      </svg>
    </div>
  </div>
</template>
