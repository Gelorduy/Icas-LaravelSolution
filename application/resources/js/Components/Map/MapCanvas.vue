<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useMapStore } from '@/Stores/mapStore';

const mapStore = useMapStore();
const svgRef = ref(null);
const viewBox = ref('0 0 1000 1000');
const isDragging = ref(false);
const dragStart = ref({ x: 0, y: 0 });

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
  const pan = mapStore.pan ?? { x: 0, y: 0 };
  
  const adjustedWidth = width / scale;
  const adjustedHeight = height / scale;
  
  viewBox.value = `${x + pan.x} ${y + pan.y} ${adjustedWidth} ${adjustedHeight}`;
};

const handleWheel = (event) => {
  if (!mapStore.manifest) return;
  
  // Ctrl + wheel for zoom
  if (event.ctrlKey) {
    event.preventDefault();
    
    const direction = event.deltaY > 0 ? -1 : 1;
    const step = 0.1 * direction;
    const currentZoom = mapStore.zoom ?? 1;
    const nextZoom = Math.max(0.1, currentZoom + step);
    
    // Get mouse position relative to SVG element
    const rect = svgRef.value.getBoundingClientRect();
    const mouseX = event.clientX - rect.left;
    const mouseY = event.clientY - rect.top;
    
    // Convert mouse position to SVG coordinates before zoom
    const { x, y, width, height } = normalizeBounds();
    const currentPan = mapStore.pan ?? { x: 0, y: 0 };
    const adjustedWidth = width / currentZoom;
    const adjustedHeight = height / currentZoom;
    
    const svgMouseX = (mouseX / rect.width) * adjustedWidth + x + currentPan.x;
    const svgMouseY = (mouseY / rect.height) * adjustedHeight + y + currentPan.y;
    
    // Calculate new pan to keep the mouse position fixed
    const newAdjustedWidth = width / nextZoom;
    const newAdjustedHeight = height / nextZoom;
    
    const newPanX = svgMouseX - (mouseX / rect.width) * newAdjustedWidth - x;
    const newPanY = svgMouseY - (mouseY / rect.height) * newAdjustedHeight - y;
    
    mapStore.setZoom(nextZoom);
    mapStore.setPan({ x: newPanX, y: newPanY });
    return;
  }
  
  // Shift + wheel for horizontal panning
  if (event.shiftKey) {
    const direction = event.deltaY > 0 ? 1 : -1;
    const panStep = 50 * direction;
    const currentPan = mapStore.pan ?? { x: 0, y: 0 };
    mapStore.setPan({
      x: currentPan.x + panStep,
      y: currentPan.y,
    });
    return;
  }
  
  // Regular wheel for vertical panning
  const direction = event.deltaY > 0 ? 1 : -1;
  const panStep = 50 * direction;
  const currentPan = mapStore.pan ?? { x: 0, y: 0 };
  mapStore.setPan({
    x: currentPan.x,
    y: currentPan.y + panStep,
  });
};

const handleMouseDown = (event) => {
  if (event.button !== 0) return; // Only left mouse button
  isDragging.value = true;
  dragStart.value = { x: event.clientX, y: event.clientY };
  event.preventDefault();
};

const handleMouseMove = (event) => {
  if (!isDragging.value) return;
  
  const deltaX = event.clientX - dragStart.value.x;
  const deltaY = event.clientY - dragStart.value.y;
  
  // Update drag start for next movement
  dragStart.value = { x: event.clientX, y: event.clientY };
  
  // Apply pan based on zoom level (more zoom = less pan per pixel)
  const scale = mapStore.zoom ?? 1;
  const currentPan = mapStore.pan ?? { x: 0, y: 0 };
  
  mapStore.setPan({
    x: currentPan.x - (deltaX / scale),
    y: currentPan.y - (deltaY / scale),
  });
};

const handleMouseUp = () => {
  isDragging.value = false;
};

const handleMouseLeave = () => {
  isDragging.value = false;
};

const getLayerOpacity = (layer) => layer.style_preset?.opacity ?? 1;
const getOverlayPath = (layer) => layer.data_source?.svg_path ?? layer.data_source?.url;
const getGeoFeatures = (layer) => layer.data_source?.features ?? [];
const getDevices = (layer) => layer.data_source?.devices ?? [];
const getLayerElements = (layer) => mapStore.layerElements[layer.id] ?? [];
const deviceFill = (device) => (device.status === 'critical' ? '#ef4444' : '#38bdf8');
const deviceRadius = (device) => (device.status === 'critical' ? 12 : 8);

const getIconClass = (iconValue) => {
  const iconMap = {
    'fire-alarm': 'pi-bolt',
    'exit': 'pi-sign-out',
    'camera': 'pi-camera',
    'sensor': 'pi-eye',
    'door': 'pi-box',
    'window': 'pi-window-maximize'
  };
  return iconMap[iconValue] || 'pi-image';
};

const getIconColor = (iconValue) => {
  const colorMap = {
    'fire-alarm': '#ef4444',
    'exit': '#22c55e',
    'camera': '#3b82f6',
    'sensor': '#a855f7',
    'door': '#f59e0b',
    'window': '#06b6d4'
  };
  return colorMap[iconValue] || '#3b82f6';
};

onMounted(() => {
  applyViewportToViewBox();
});

watch(activeViewport, applyViewportToViewBox);
watch(() => mapStore.zoomLevel, applyViewportToViewBox, { deep: true });
watch(() => mapStore.pan, applyViewportToViewBox, { deep: true });
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
      :class="{ 'cursor-grabbing': isDragging, 'cursor-grab': !isDragging }"
      @wheel.prevent="handleWheel"
      @mousedown="handleMouseDown"
      @mousemove="handleMouseMove"
      @mouseup="handleMouseUp"
      @mouseleave="handleMouseLeave"
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

          <!-- Render layer elements (from layer editor) -->
          <g v-if="getLayerElements(layer).length > 0">
            <template v-for="element in getLayerElements(layer)" :key="element.id">
              <!-- Text elements -->
              <text
                v-if="element.type === 'text'"
                :x="element.x"
                :y="element.y"
                class="fill-white"
                font-size="16"
                font-weight="500"
              >
                {{ element.content }}
              </text>

              <!-- Marker elements (circles) -->
              <circle
                v-if="element.type === 'marker'"
                :cx="element.x"
                :cy="element.y"
                r="10"
                class="fill-blue-500"
                opacity="0.9"
              />

              <!-- SVG Icon elements -->
              <g v-if="element.type === 'svg_icon'">
                <rect
                  :x="element.x - 16"
                  :y="element.y - 16"
                  width="32"
                  height="32"
                  :fill="getIconColor(element.icon)"
                  rx="4"
                  opacity="0.9"
                />
                <foreignObject
                  :x="element.x - 12"
                  :y="element.y - 12"
                  width="24"
                  height="24"
                >
                  <div xmlns="http://www.w3.org/1999/xhtml" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                    <i :class="`pi ${getIconClass(element.icon)} text-white`" style="font-size: 18px;"></i>
                  </div>
                </foreignObject>
              </g>

              <!-- Polygon elements -->
              <polygon
                v-if="element.type === 'polygon'"
                :points="`${element.x},${element.y} ${element.x+20},${element.y} ${element.x+10},${element.y+20}`"
                class="fill-purple-500"
                opacity="0.7"
              />

              <!-- Line elements -->
              <line
                v-if="element.type === 'line'"
                :x1="element.x"
                :y1="element.y"
                :x2="element.x + 50"
                :y2="element.y"
                class="stroke-orange-500"
                stroke-width="3"
                opacity="0.8"
              />

              <!-- Circle elements -->
              <circle
                v-if="element.type === 'circle'"
                :cx="element.x"
                :cy="element.y"
                r="15"
                class="fill-pink-500"
                opacity="0.7"
              />
            </template>
          </g>

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
