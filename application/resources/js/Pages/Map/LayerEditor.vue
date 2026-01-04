<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import IcasLayout from '@/Layouts/IcasLayout.vue';
import Button from 'primevue/button';
import Toolbar from 'primevue/toolbar';
import Sidebar from 'primevue/sidebar';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import MultiSelect from 'primevue/multiselect';
import Textarea from 'primevue/textarea';
import InputNumber from 'primevue/inputnumber';
import Checkbox from 'primevue/checkbox';
import Divider from 'primevue/divider';
import ContextMenu from 'primevue/contextmenu';

const props = defineProps({
    map: {
        type: Object,
        required: true
    },
    layer: {
        type: Object,
        required: true
    },
    manifest: {
        type: Object,
        default: () => ({ layers: [] })
    }
});

const svgCanvas = ref(null);
const canvasContainer = ref(null);
const selectedTool = ref('select'); // select, icon, text, marker
const elements = ref([]);
const parentLayerElements = ref([]);
const showPropertiesDialog = ref(false);
const showElementsPanel = ref(true);
const showLayersPanel = ref(true);
const draggedElement = ref(null);
const selectedElement = ref(null);
const hoveredElement = ref(null);
const tooltipPosition = ref({ x: 0, y: 0 });
const showContextMenu = ref(false);
const contextMenuPosition = ref({ x: 0, y: 0 });
const contextMenuElement = ref(null);

// Element dragging state
const isDraggingElement = ref(false);
const draggedElementId = ref(null);
const dragOffset = ref({ x: 0, y: 0 });

// Pan and zoom state
const viewBox = ref({ x: 0, y: 0, width: 1920, height: 1080 });
const isPanning = ref(false);
const panStart = ref({ x: 0, y: 0 });
const zoomLevel = ref(1);

// Layer visibility state
const layerVisibility = ref({
    parent: true,
    current: true
});

// Layer properties form
const layerForm = ref({
    display_name: props.layer.display_name,
    layer_type: props.layer.layer_type,
    element_types: props.layer.element_types || [],
    parent_layer_id: props.layer.parent_layer_id,
    related_layers: props.layer.related_layers || [],
    description: props.layer.style_preset?.description || '',
    z_index: props.layer.z_index,
    default_visible: props.layer.default_visible
});

const tools = [
    { label: 'Select', value: 'select', icon: 'pi-cursor' },
    { label: 'Add Icon', value: 'icon', icon: 'pi-image' },
    { label: 'Add Text', value: 'text', icon: 'pi-font' },
    { label: 'Add Marker', value: 'marker', icon: 'pi-map-marker' }
];

const layerTypes = [
    { label: 'Overlay', value: 'overlay' },
    { label: 'Base', value: 'base' },
    { label: 'Annotation', value: 'annotation' },
    { label: 'Data', value: 'data' }
];

const elementTypes = [
    { label: 'SVG Icon', value: 'svg_icon', icon: 'pi-image', color: 'bg-blue-500' },
    { label: 'Text', value: 'text', icon: 'pi-font', color: 'bg-green-500' },
    { label: 'Marker', value: 'marker', icon: 'pi-map-marker', color: 'bg-red-500' },
    { label: 'Polygon', value: 'polygon', icon: 'pi-stop', color: 'bg-purple-500' },
    { label: 'Line', value: 'line', icon: 'pi-minus', color: 'bg-orange-500' },
    { label: 'Circle', value: 'circle', icon: 'pi-circle', color: 'bg-pink-500' }
];

const iconOptions = [
    { label: 'Fire Alarm', value: 'fire-alarm', icon: 'pi-bolt' },
    { label: 'Exit', value: 'exit', icon: 'pi-sign-out' },
    { label: 'Camera', value: 'camera', icon: 'pi-camera' },
    { label: 'Sensor', value: 'sensor', icon: 'pi-eye' },
    { label: 'Door', value: 'door', icon: 'pi-box' },
    { label: 'Window', value: 'window', icon: 'pi-window-maximize' }
];

const availableLayers = computed(() => {
    return (props.manifest?.layers || [])
        .filter(l => l.id !== props.layer.id)
        .map(l => ({ 
            label: l.display_name, 
            value: l.id 
        }));
});

const parentLayer = computed(() => {
    if (!props.layer.parent_layer_id) return null;
    return props.manifest?.layers?.find(l => l.id === props.layer.parent_layer_id);
});

const relatedLayersList = computed(() => {
    if (!props.layer.related_layers?.length) return [];
    return props.manifest?.layers?.filter(l => 
        props.layer.related_layers.includes(l.id)
    ) || [];
});

const handleCanvasClick = (event) => {
    if (selectedTool.value === 'select' || isPanning.value) return;
    
    // Convert screen coordinates to SVG coordinates
    const pt = svgCanvas.value.createSVGPoint();
    pt.x = event.clientX;
    pt.y = event.clientY;
    const svgPt = pt.matrixTransform(svgCanvas.value.getScreenCTM().inverse());
    const x = svgPt.x;
    const y = svgPt.y;
    
    if (selectedTool.value === 'text') {
        const text = prompt('Enter text:');
        if (text) {
            elements.value.push({
                type: 'text',
                x,
                y,
                content: text,
                id: Date.now()
            });
        }
    } else if (selectedTool.value === 'icon' || selectedTool.value === 'marker') {
        elements.value.push({
            type: selectedTool.value,
            x,
            y,
            icon: 'fire-alarm',
            id: Date.now()
        });
    }
};

const handleDragStart = (elementType) => {
    draggedElement.value = elementType;
};

const handleDragOver = (event) => {
    event.preventDefault();
};

const handleDrop = (event) => {
    event.preventDefault();
    if (!draggedElement.value) return;
    
    // Convert screen coordinates to SVG coordinates
    const pt = svgCanvas.value.createSVGPoint();
    pt.x = event.clientX;
    pt.y = event.clientY;
    const svgPt = pt.matrixTransform(svgCanvas.value.getScreenCTM().inverse());
    const x = svgPt.x;
    const y = svgPt.y;
    
    if (draggedElement.value.value === 'text') {
        const text = prompt('Enter text:');
        if (text) {
            elements.value.push({
                type: 'text',
                x,
                y,
                content: text,
                id: Date.now()
            });
        }
    } else {
        elements.value.push({
            type: draggedElement.value.value,
            x,
            y,
            icon: draggedElement.value.icon || 'fire-alarm',
            id: Date.now()
        });
    }
    
    draggedElement.value = null;
};

const updateLayerProperties = async () => {
    try {
        await axios.put(`/api/maps/${props.map.id}/layers/${props.layer.id}`, layerForm.value);
        showPropertiesDialog.value = false;
        alert('Layer properties updated successfully!');
        router.reload({ only: ['layer'] });
    } catch (error) {
        console.error('Failed to update layer properties:', error);
        alert('Failed to update layer properties');
    }
};

const toggleLayerVisibility = async (layerId, isParent = false) => {
    try {
        // Update local visibility state immediately for UI responsiveness
        if (isParent) {
            layerVisibility.value.parent = !layerVisibility.value.parent;
        } else {
            // For related layers, update via API
            await axios.post(`/api/maps/${props.map.id}/layers/${layerId}/toggle`);
            router.reload({ only: ['manifest'] });
        }
    } catch (error) {
        console.error('Failed to toggle layer visibility:', error);
    }
};

const handleElementClick = (element, event) => {
    if (selectedTool.value === 'select' && !isPanning.value) {
        event.stopPropagation();
        selectedElement.value = element;
    }
};

const handleElementMouseDown = (element, event) => {
    if (selectedTool.value === 'select' && event.button === 0) {
        event.stopPropagation();
        event.preventDefault();
        isDraggingElement.value = true;
        draggedElementId.value = element.id;
        
        // Calculate offset from element position to mouse position
        const pt = svgCanvas.value.createSVGPoint();
        pt.x = event.clientX;
        pt.y = event.clientY;
        const svgPt = pt.matrixTransform(svgCanvas.value.getScreenCTM().inverse());
        
        dragOffset.value = {
            x: svgPt.x - element.x,
            y: svgPt.y - element.y
        };
    }
};

const handleElementMouseEnter = (element, event) => {
    hoveredElement.value = element;
    // Convert SVG coordinates to screen coordinates
    const rect = svgCanvas.value.getBoundingClientRect();
    const svgX = element.x;
    const svgY = element.y;
    
    // Calculate screen position
    const scaleX = rect.width / viewBox.value.width;
    const scaleY = rect.height / viewBox.value.height;
    
    const screenX = rect.left + (svgX - viewBox.value.x) * scaleX;
    const screenY = rect.top + (svgY - viewBox.value.y) * scaleY;
    
    tooltipPosition.value = { x: screenX, y: screenY };
};

const handleElementMouseLeave = () => {
    hoveredElement.value = null;
};

const handleElementContextMenu = (element, event) => {
    event.preventDefault();
    event.stopPropagation();
    showContextMenu.value = true;
    contextMenuElement.value = element;
    contextMenuPosition.value = { x: event.clientX, y: event.clientY };
};

const deleteElement = () => {
    if (contextMenuElement.value) {
        elements.value = elements.value.filter(el => el.id !== contextMenuElement.value.id);
        closeContextMenu();
    }
};

const editElementProperties = () => {
    if (contextMenuElement.value) {
        selectedElement.value = contextMenuElement.value;
        // Here you could open a properties dialog
        alert('Edit properties for: ' + (contextMenuElement.value.content || contextMenuElement.value.icon || contextMenuElement.value.type));
        closeContextMenu();
    }
};

const closeContextMenu = () => {
    showContextMenu.value = false;
    contextMenuElement.value = null;
};

const saveLayer = async () => {
    try {
        // Transform elements to match backend schema
        const transformedElements = elements.value.map(element => ({
            element_type: element.type,
            geometry: {
                x: element.x,
                y: element.y
            },
            payload: {
                content: element.content || null,
                icon: element.icon || null
            },
            state: {
                id: element.id
            }
        }));

        // Save elements to the layer
        await axios.post(`/api/layers/${props.layer.id}/elements`, {
            elements: transformedElements
        });
        alert('Layer saved successfully!');
    } catch (error) {
        console.error('Failed to save layer:', error);
        if (error.response?.data?.errors) {
            console.error('Validation errors:', error.response.data.errors);
        }
        alert('Failed to save layer');
    }
};

const goBack = () => {
    router.visit(route('map.index', { site: props.map.site_id, map: props.map.id }));
};

// Pan and zoom functions
const handleMouseDown = (event) => {
    if (event.button === 0 && selectedTool.value === 'select') {
        // Left mouse button for dragging/panning when select tool is active
        event.preventDefault();
        isPanning.value = true;
        panStart.value = { x: event.clientX, y: event.clientY };
    }
};

const handleMouseMove = (event) => {
    // Handle element dragging first (higher priority)
    if (isDraggingElement.value && draggedElementId.value) {
        event.preventDefault();
        
        // Convert screen coordinates to SVG coordinates
        const pt = svgCanvas.value.createSVGPoint();
        pt.x = event.clientX;
        pt.y = event.clientY;
        const svgPt = pt.matrixTransform(svgCanvas.value.getScreenCTM().inverse());
        
        // Find and update the element position
        const elementIndex = elements.value.findIndex(el => el.id === draggedElementId.value);
        if (elementIndex !== -1) {
            elements.value[elementIndex] = {
                ...elements.value[elementIndex],
                x: svgPt.x - dragOffset.value.x,
                y: svgPt.y - dragOffset.value.y
            };
        }
        return;
    }
    
    // Handle canvas panning
    if (!isPanning.value) return;
    
    event.preventDefault();
    const deltaX = event.clientX - panStart.value.x;
    const deltaY = event.clientY - panStart.value.y;
    
    // Scale the delta by the current zoom level
    const scale = 1 / zoomLevel.value;
    
    viewBox.value = {
        ...viewBox.value,
        x: viewBox.value.x - (deltaX * scale),
        y: viewBox.value.y - (deltaY * scale)
    };
    
    panStart.value = { x: event.clientX, y: event.clientY };
};

const handleMouseUp = () => {
    isPanning.value = false;
    isDraggingElement.value = false;
    draggedElementId.value = null;
};

const handleWheel = (event) => {
    // Ctrl + wheel for zoom
    if (event.ctrlKey) {
        event.preventDefault();
        
        const direction = event.deltaY > 0 ? -1 : 1;
        const step = 0.1 * direction;
        const nextZoom = Math.max(0.1, zoomLevel.value + step);
        
        // Get mouse position relative to SVG element
        const rect = svgCanvas.value.getBoundingClientRect();
        const mouseX = event.clientX - rect.left;
        const mouseY = event.clientY - rect.top;
        
        // Convert mouse position to SVG coordinates before zoom
        const svgMouseX = (mouseX / rect.width) * viewBox.value.width + viewBox.value.x;
        const svgMouseY = (mouseY / rect.height) * viewBox.value.height + viewBox.value.y;
        
        // Calculate new viewBox dimensions
        const newWidth = 1920 / nextZoom;
        const newHeight = 1080 / nextZoom;
        
        // Calculate new pan to keep the mouse position fixed
        const newX = svgMouseX - (mouseX / rect.width) * newWidth;
        const newY = svgMouseY - (mouseY / rect.height) * newHeight;
        
        viewBox.value = {
            x: newX,
            y: newY,
            width: newWidth,
            height: newHeight
        };
        
        zoomLevel.value = nextZoom;
        return;
    }
    
    // Shift + wheel for horizontal panning
    if (event.shiftKey) {
        event.preventDefault();
        const direction = event.deltaY > 0 ? 1 : -1;
        const panStep = 50;
        viewBox.value = {
            ...viewBox.value,
            x: viewBox.value.x + (panStep * direction)
        };
        return;
    }
    
    // Regular wheel for vertical panning
    event.preventDefault();
    const direction = event.deltaY > 0 ? 1 : -1;
    const panStep = 50;
    viewBox.value = {
        ...viewBox.value,
        y: viewBox.value.y + (panStep * direction)
    };
};

const resetView = () => {
    viewBox.value = { x: 0, y: 0, width: 1920, height: 1080 };
    zoomLevel.value = 1;
};

const zoomIn = () => {
    const delta = 0.8;
    const centerX = viewBox.value.x + viewBox.value.width / 2;
    const centerY = viewBox.value.y + viewBox.value.height / 2;
    
    const newWidth = viewBox.value.width * delta;
    const newHeight = viewBox.value.height * delta;
    
    viewBox.value = {
        x: centerX - newWidth / 2,
        y: centerY - newHeight / 2,
        width: newWidth,
        height: newHeight
    };
    
    zoomLevel.value = 1920 / newWidth;
};

const zoomOut = () => {
    const delta = 1.25;
    const centerX = viewBox.value.x + viewBox.value.width / 2;
    const centerY = viewBox.value.y + viewBox.value.height / 2;
    
    const newWidth = viewBox.value.width * delta;
    const newHeight = viewBox.value.height * delta;
    
    viewBox.value = {
        x: centerX - newWidth / 2,
        y: centerY - newHeight / 2,
        width: newWidth,
        height: newHeight
    };
    
    zoomLevel.value = 1920 / newWidth;
};

const viewBoxString = computed(() => {
    return `${viewBox.value.x} ${viewBox.value.y} ${viewBox.value.width} ${viewBox.value.height}`;
});

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

// Load existing elements from the database
const loadElements = async () => {
    try {
        const response = await axios.get(`/api/layers/${props.layer.id}/elements`);
        if (response.data && Array.isArray(response.data)) {
            // Transform database elements to frontend format
            elements.value = response.data.map(dbElement => ({
                id: dbElement.state?.id || Date.now() + Math.random(),
                type: dbElement.element_type,
                x: dbElement.geometry.x,
                y: dbElement.geometry.y,
                content: dbElement.payload?.content || null,
                icon: dbElement.payload?.icon || null
            }));
            console.log('Loaded elements:', elements.value);
        }
    } catch (error) {
        console.error('Failed to load elements:', error);
        // Not a critical error, just start with empty elements
    }
};

// Load parent layer elements if there's a parent layer
const loadParentLayerElements = async () => {
    if (!props.layer.parent_layer_id) return;
    
    try {
        const response = await axios.get(`/api/layers/${props.layer.parent_layer_id}/elements`);
        if (response.data && Array.isArray(response.data)) {
            parentLayerElements.value = response.data.map(dbElement => ({
                id: dbElement.state?.id || Date.now() + Math.random(),
                type: dbElement.element_type,
                x: dbElement.geometry.x,
                y: dbElement.geometry.y,
                content: dbElement.payload?.content || null,
                icon: dbElement.payload?.icon || null
            }));
            console.log('Loaded parent layer elements:', parentLayerElements.value);
        }
    } catch (error) {
        console.error('Failed to load parent layer elements:', error);
    }
};

// Load layer data source if available
onMounted(() => {
    console.log('Layer data:', props.layer);
    console.log('Map data:', props.map);
    if (props.layer.data_source?.svg_path) {
        console.log('SVG Path:', props.layer.data_source.svg_path);
    }
    
    // Load existing elements
    loadElements();
    
    // Load parent layer elements if parent exists
    loadParentLayerElements();
});
</script>

<template>
    <IcasLayout :title="`Edit Layer: ${layer.display_name}`">
        <div class="h-[calc(100vh-4rem)] flex flex-col">
            <!-- Toolbar -->
            <Toolbar class="border-b border-surface-200 dark:border-surface-700">
                <template #start>
                    <Button 
                        icon="pi pi-arrow-left" 
                        text 
                        @click="goBack"
                        label="Back to Map"
                    />
                    <div class="ml-4 font-semibold text-lg">
                        {{ layer.display_name }}
                    </div>
                </template>
                <template #center>
                    <div class="flex gap-2">
                        <Button 
                            v-for="tool in tools" 
                            :key="tool.value"
                            :icon="`pi ${tool.icon}`"
                            :label="tool.label"
                            :severity="selectedTool === tool.value ? 'primary' : 'secondary'"
                            @click="selectedTool = tool.value"
                            text
                        />
                    </div>
                </template>
                <template #end>
                    <Button 
                        label="Properties" 
                        icon="pi pi-cog" 
                        @click="showPropertiesDialog = true"
                        class="mr-2"
                        outlined
                    />
                    <Button 
                        label="Save Layer" 
                        icon="pi pi-save" 
                        @click="saveLayer"
                    />
                </template>
            </Toolbar>

            <!-- Main Content Area -->
            <div class="flex-1 flex overflow-hidden">
                <!-- Left Panel: Element Types (Inkscape-style) -->
                <div 
                    v-if="showElementsPanel"
                    class="w-64 border-r border-surface-200 dark:border-surface-700 bg-surface-0 dark:bg-surface-800 overflow-y-auto"
                >
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-surface-900 dark:text-surface-0">
                                Element Types
                            </h3>
                            <Button 
                                icon="pi pi-times" 
                                text 
                                rounded 
                                size="small"
                                @click="showElementsPanel = false"
                            />
                        </div>
                        <Divider />
                        
                        <!-- Draggable Element Types -->
                        <div class="space-y-2">
                            <div
                                v-for="elementType in elementTypes"
                                :key="elementType.value"
                                draggable="true"
                                @dragstart="handleDragStart(elementType)"
                                class="p-3 border-2 border-surface-200 dark:border-surface-600 rounded-lg cursor-move hover:border-primary hover:bg-surface-50 dark:hover:bg-surface-700 transition-colors"
                            >
                                <div class="flex items-center gap-3">
                                    <div :class="`w-10 h-10 rounded ${elementType.color} flex items-center justify-center`">
                                        <i :class="`pi ${elementType.icon} text-white`"></i>
                                    </div>
                                    <span class="font-medium text-surface-900 dark:text-surface-0">
                                        {{ elementType.label }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <Divider />
                        
                        <!-- SVG Icons Library -->
                        <h4 class="font-semibold text-sm text-surface-700 dark:text-surface-300 mb-2">
                            SVG Icons
                        </h4>
                        <div class="grid grid-cols-3 gap-2">
                            <div
                                v-for="icon in iconOptions"
                                :key="icon.value"
                                draggable="true"
                                @dragstart="handleDragStart({ value: 'svg_icon', icon: icon.value })"
                                class="aspect-square border-2 border-surface-200 dark:border-surface-600 rounded cursor-move hover:border-primary hover:bg-surface-50 dark:hover:bg-surface-700 flex items-center justify-center transition-colors"
                                :title="icon.label"
                            >
                                <i :class="`pi ${icon.icon} text-xl`"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Canvas Area -->
                <div 
                    ref="canvasContainer"
                    class="flex-1 relative bg-surface-100 dark:bg-surface-900 overflow-hidden"
                    @mousedown="handleMouseDown"
                    @mousemove="handleMouseMove"
                    @mouseup="handleMouseUp"
                    @mouseleave="handleMouseUp"
                    @wheel="handleWheel"
                    @click="showContextMenu && closeContextMenu()"
                >
                    <svg 
                        ref="svgCanvas"
                        class="w-full h-full"
                        :style="{ cursor: isDraggingElement ? 'grabbing' : (isPanning ? 'grabbing' : (selectedTool === 'select' ? 'default' : 'crosshair')) }"
                        @click="handleCanvasClick"
                        @dragover="handleDragOver"
                        @drop="handleDrop"
                        :viewBox="viewBoxString"
                        preserveAspectRatio="xMidYMid meet"
                    >
                        <!-- Parent layer SVG if available -->
                        <image 
                            v-if="parentLayer?.data_source?.svg_path && layerVisibility.parent"
                            :href="parentLayer.data_source.svg_path"
                            x="0"
                            y="0"
                            width="1920"
                            height="1080"
                            opacity="0.3"
                            preserveAspectRatio="xMidYMid meet"
                        />
                        
                        <!-- Parent layer elements (read-only reference) -->
                        <g v-if="layerVisibility.parent && parentLayerElements.length > 0" opacity="0.5">
                            <template v-for="element in parentLayerElements" :key="'parent-' + element.id">
                                <!-- Text elements -->
                                <text
                                    v-if="element.type === 'text'"
                                    :x="element.x"
                                    :y="element.y"
                                    class="fill-current text-surface-400 dark:text-surface-500 pointer-events-none"
                                    font-size="16"
                                >
                                    {{ element.content }}
                                </text>

                                <!-- Marker elements -->
                                <circle
                                    v-if="element.type === 'marker'"
                                    :cx="element.x"
                                    :cy="element.y"
                                    r="8"
                                    class="fill-blue-400 pointer-events-none"
                                />

                                <!-- SVG Icon elements -->
                                <g v-if="element.type === 'svg_icon' || element.type === 'icon'" class="pointer-events-none">
                                    <rect
                                        :x="element.x - 16"
                                        :y="element.y - 16"
                                        width="32"
                                        height="32"
                                        :fill="getIconColor(element.icon)"
                                        rx="4"
                                        opacity="0.7"
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
                                    class="fill-purple-400 pointer-events-none"
                                />

                                <!-- Line elements -->
                                <line
                                    v-if="element.type === 'line'"
                                    :x1="element.x"
                                    :y1="element.y"
                                    :x2="element.x + 50"
                                    :y2="element.y"
                                    class="stroke-orange-400 pointer-events-none"
                                    stroke-width="3"
                                />

                                <!-- Circle elements -->
                                <circle
                                    v-if="element.type === 'circle'"
                                    :cx="element.x"
                                    :cy="element.y"
                                    r="15"
                                    class="fill-pink-400 pointer-events-none"
                                />
                            </template>
                        </g>
                        
                        <!-- Current layer SVG if available -->
                        <image 
                            v-if="layer.data_source?.svg_path && layerVisibility.current"
                            :href="layer.data_source.svg_path"
                            x="0"
                            y="0"
                            width="1920"
                            height="1080"
                            preserveAspectRatio="xMidYMid meet"
                        />
                        
                        <!-- Render elements -->
                        <g v-for="element in elements" :key="element.id">
                            <text 
                                v-if="element.type === 'text'"
                                :x="element.x"
                                :y="element.y"
                                class="fill-current text-surface-900 dark:text-surface-0"
                                :class="{ 
                                    'stroke-primary stroke-2': selectedElement?.id === element.id,
                                    'cursor-move': selectedTool === 'select',
                                    'cursor-pointer': selectedTool !== 'select'
                                }"
                                font-size="16"
                                @mousedown="handleElementMouseDown(element, $event)"
                                @click="handleElementClick(element, $event)"
                                @mouseenter="handleElementMouseEnter(element, $event)"
                                @mouseleave="handleElementMouseLeave"
                                @contextmenu="handleElementContextMenu(element, $event)"
                            >
                                {{ element.content }}
                            </text>
                            
                            <circle
                                v-if="element.type === 'marker'"
                                :cx="element.x"
                                :cy="element.y"
                                r="8"
                                class="fill-red-500"
                                :class="{ 
                                    'stroke-primary stroke-2': selectedElement?.id === element.id,
                                    'cursor-move': selectedTool === 'select',
                                    'cursor-pointer': selectedTool !== 'select'
                                }"
                                @mousedown="handleElementMouseDown(element, $event)"
                                @click="handleElementClick(element, $event)"
                                @mouseenter="handleElementMouseEnter(element, $event)"
                                @mouseleave="handleElementMouseLeave"
                                @contextmenu="handleElementContextMenu(element, $event)"
                            />
                            
                            <!-- SVG Icon with proper rendering using foreignObject -->
                            <g
                                v-if="element.type === 'svg_icon' || element.type === 'icon'"
                                :class="{
                                    'cursor-move': selectedTool === 'select',
                                    'cursor-pointer': selectedTool !== 'select'
                                }"
                                @mousedown="handleElementMouseDown(element, $event)"
                                @click="handleElementClick(element, $event)"
                                @mouseenter="handleElementMouseEnter(element, $event)"
                                @mouseleave="handleElementMouseLeave"
                                @contextmenu="handleElementContextMenu(element, $event)"
                            >
                                <rect
                                    :x="element.x - 16"
                                    :y="element.y - 16"
                                    width="32"
                                    height="32"
                                    :fill="getIconColor(element.icon)"
                                    rx="4"
                                    :stroke="selectedElement?.id === element.id ? '#3b82f6' : 'none'"
                                    stroke-width="2"
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
                            
                            <polygon
                                v-if="element.type === 'polygon'"
                                :points="`${element.x},${element.y} ${element.x+20},${element.y} ${element.x+10},${element.y+20}`"
                                class="fill-purple-500"
                                :class="{ 
                                    'stroke-primary stroke-2': selectedElement?.id === element.id,
                                    'cursor-move': selectedTool === 'select',
                                    'cursor-pointer': selectedTool !== 'select'
                                }"
                                @mousedown="handleElementMouseDown(element, $event)"
                                @click="handleElementClick(element, $event)"
                                @mouseenter="handleElementMouseEnter(element, $event)"
                                @mouseleave="handleElementMouseLeave"
                                @contextmenu="handleElementContextMenu(element, $event)"
                            />
                            
                            <line
                                v-if="element.type === 'line'"
                                :x1="element.x"
                                :y1="element.y"
                                :x2="element.x + 50"
                                :y2="element.y"
                                class="stroke-orange-500"
                                :class="{ 
                                    'stroke-primary stroke-4': selectedElement?.id === element.id,
                                    'cursor-move': selectedTool === 'select',
                                    'cursor-pointer': selectedTool !== 'select'
                                }"
                                stroke-width="3"
                                @mousedown="handleElementMouseDown(element, $event)"
                                @click="handleElementClick(element, $event)"
                                @mouseenter="handleElementMouseEnter(element, $event)"
                                @mouseleave="handleElementMouseLeave"
                                @contextmenu="handleElementContextMenu(element, $event)"
                            />
                            
                            <circle
                                v-if="element.type === 'circle'"
                                :cx="element.x"
                                :cy="element.y"
                                r="15"
                                class="fill-pink-500"
                                :class="{ 
                                    'stroke-primary stroke-2': selectedElement?.id === element.id,
                                    'cursor-move': selectedTool === 'select',
                                    'cursor-pointer': selectedTool !== 'select'
                                }"
                                @mousedown="handleElementMouseDown(element, $event)"
                                @click="handleElementClick(element, $event)"
                                @mouseenter="handleElementMouseEnter(element, $event)"
                                @mouseleave="handleElementMouseLeave"
                                @contextmenu="handleElementContextMenu(element, $event)"
                            />
                        </g>
                    </svg>
                    
                    <!-- Element Info Tooltip -->
                    <div 
                        v-if="hoveredElement"
                        class="fixed pointer-events-none bg-surface-0 dark:bg-surface-800 border border-surface-200 dark:border-surface-700 rounded-lg shadow-lg px-3 py-2 text-xs z-50"
                        :style="{
                            left: `${tooltipPosition.x + 20}px`,
                            top: `${tooltipPosition.y - 60}px`
                        }"
                    >
                        <div class="font-semibold text-surface-900 dark:text-surface-0">
                            {{ hoveredElement.content || hoveredElement.icon || hoveredElement.type }}
                        </div>
                        <div class="text-surface-600 dark:text-surface-400 space-y-0.5">
                            <div>ID: {{ hoveredElement.id }}</div>
                            <div>Type: {{ hoveredElement.type }}</div>
                            <div>Position: ({{ Math.round(hoveredElement.x) }}, {{ Math.round(hoveredElement.y) }})</div>
                        </div>
                    </div>
                    
                    <!-- Context Menu -->
                    <div
                        v-if="showContextMenu"
                        class="fixed bg-surface-0 dark:bg-surface-800 border border-surface-200 dark:border-surface-700 rounded-lg shadow-xl z-50 py-1 min-w-[180px]"
                        :style="{
                            left: `${contextMenuPosition.x}px`,
                            top: `${contextMenuPosition.y}px`
                        }"
                        @click.stop
                    >
                        <button
                            @click="editElementProperties"
                            class="w-full px-4 py-2 text-left text-sm hover:bg-surface-100 dark:hover:bg-surface-700 flex items-center gap-2 transition-colors"
                        >
                            <i class="pi pi-pencil"></i>
                            <span>Edit Properties</span>
                        </button>
                        <button
                            @click="deleteElement"
                            class="w-full px-4 py-2 text-left text-sm hover:bg-surface-100 dark:hover:bg-surface-700 flex items-center gap-2 text-red-600 transition-colors"
                        >
                            <i class="pi pi-trash"></i>
                            <span>Delete</span>
                        </button>
                    </div>
                    
                    <!-- Instructions overlay -->
                    <div 
                        v-if="!isPanning"
                        class="absolute top-4 left-1/2 -translate-x-1/2 bg-surface-0/90 dark:bg-surface-800/90 px-4 py-2 rounded-lg shadow-lg text-sm space-y-1"
                    >
                        <div v-if="selectedTool === 'select'" class="text-xs text-center">
                            <div><strong>Pan:</strong> Click and drag</div>
                            <div><strong>Zoom:</strong> Ctrl + Mouse wheel</div>
                            <div><strong>Pan Horizontal:</strong> Shift + Mouse wheel</div>
                            <div><strong>Pan Vertical:</strong> Mouse wheel</div>
                        </div>
                        <div v-else class="text-center">
                            Click on the canvas to add {{ selectedTool }}
                        </div>
                    </div>
                    
                    <!-- Zoom controls -->
                    <div class="absolute bottom-4 right-4 flex flex-col gap-2">
                        <Button 
                            icon="pi pi-search-plus" 
                            @click="zoomIn"
                            severity="secondary"
                            rounded
                            v-tooltip.left="'Zoom In'"
                        />
                        <Button 
                            icon="pi pi-search-minus" 
                            @click="zoomOut"
                            severity="secondary"
                            rounded
                            v-tooltip.left="'Zoom Out'"
                        />
                        <Button 
                            icon="pi pi-refresh" 
                            @click="resetView"
                            severity="secondary"
                            rounded
                            v-tooltip.left="'Reset View'"
                        />
                    </div>
                    
                    <!-- Zoom level indicator -->
                    <div class="absolute bottom-4 left-4 bg-surface-0/90 dark:bg-surface-800/90 px-3 py-1 rounded text-xs font-mono">
                        {{ Math.round(zoomLevel * 100) }}%
                    </div>
                    
                    <!-- Toggle panels buttons -->
                    <div class="absolute top-4 right-4 flex gap-2">
                        <Button 
                            v-if="!showElementsPanel"
                            icon="pi pi-palette" 
                            @click="showElementsPanel = true"
                            severity="secondary"
                            rounded
                            v-tooltip.left="'Show Element Types'"
                        />
                        <Button 
                            v-if="!showLayersPanel"
                            icon="pi pi-list" 
                            @click="showLayersPanel = true"
                            severity="secondary"
                            rounded
                            v-tooltip.left="'Show Layers'"
                        />
                    </div>
                </div>

                <!-- Right Panel: Parent & Related Layers (Inkscape-style) -->
                <div 
                    v-if="showLayersPanel"
                    class="w-64 border-l border-surface-200 dark:border-surface-700 bg-surface-0 dark:bg-surface-800 overflow-y-auto"
                >
                    <div class="p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-surface-900 dark:text-surface-0">
                                Layers
                            </h3>
                            <Button 
                                icon="pi pi-times" 
                                text 
                                rounded 
                                size="small"
                                @click="showLayersPanel = false"
                            />
                        </div>
                        <Divider />
                        
                        <!-- Current Layer -->
                        <div class="mb-4">
                            <h4 class="text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase mb-2">
                                Current Layer
                            </h4>
                            <div class="p-3 bg-primary-50 dark:bg-primary-900/20 border-2 border-primary rounded-lg">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-surface-900 dark:text-surface-0">
                                        {{ layer.display_name }}
                                    </span>
                                    <Button
                                        :icon="layerVisibility.current ? 'pi pi-eye' : 'pi pi-eye-slash'"
                                        :class="layerVisibility.current ? 'text-primary' : 'text-surface-400'"
                                        text
                                        rounded
                                        size="small"
                                        @click="layerVisibility.current = !layerVisibility.current"
                                    />
                                </div>
                                <div class="text-xs text-surface-600 dark:text-surface-400 mt-1">
                                    Type: {{ layer.layer_type }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Parent Layer -->
                        <div v-if="parentLayer" class="mb-4">
                            <h4 class="text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase mb-2">
                                Parent Layer
                            </h4>
                            <div class="p-3 border border-surface-200 dark:border-surface-600 rounded-lg hover:bg-surface-50 dark:hover:bg-surface-700">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-surface-900 dark:text-surface-0">
                                        {{ parentLayer.display_name }}
                                    </span>
                                    <Button
                                        :icon="layerVisibility.parent ? 'pi pi-eye' : 'pi pi-eye-slash'"
                                        :class="layerVisibility.parent ? 'text-primary' : 'text-surface-400'"
                                        text
                                        rounded
                                        size="small"
                                        @click="toggleLayerVisibility(parentLayer.id, true)"
                                    />
                                </div>
                            </div>
                        </div>
                        
                        <!-- Related Layers -->
                        <div v-if="relatedLayersList.length > 0">
                            <h4 class="text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase mb-2">
                                Related Layers
                            </h4>
                            <div class="space-y-2">
                                <div
                                    v-for="relLayer in relatedLayersList"
                                    :key="relLayer.id"
                                    class="p-3 border border-surface-200 dark:border-surface-600 rounded-lg hover:bg-surface-50 dark:hover:bg-surface-700"
                                >
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium text-surface-900 dark:text-surface-0">
                                            {{ relLayer.display_name }}
                                        </span>
                                        <Button
                                            :icon="relLayer.default_visible ? 'pi pi-eye' : 'pi pi-eye-slash'"
                                            text
                                            rounded
                                            size="small"
                                            @click="toggleLayerVisibility(relLayer.id)"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <Divider v-if="!parentLayer && relatedLayersList.length === 0" />
                        
                        <!-- Empty state -->
                        <div 
                            v-if="!parentLayer && relatedLayersList.length === 0"
                            class="text-center text-surface-500 dark:text-surface-400 text-sm py-4"
                        >
                            <i class="pi pi-inbox text-2xl mb-2"></i>
                            <p>No parent or related layers</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Properties Dialog -->
        <Dialog 
            v-model:visible="showPropertiesDialog" 
            modal 
            header="Layer Properties"
            :style="{ width: '600px' }"
        >
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">
                        Display Name
                    </label>
                    <InputText 
                        v-model="layerForm.display_name" 
                        class="w-full"
                        placeholder="Layer name"
                    />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">
                        Layer Type
                    </label>
                    <Dropdown 
                        v-model="layerForm.layer_type" 
                        :options="layerTypes"
                        optionLabel="label"
                        optionValue="value"
                        class="w-full"
                        placeholder="Select type"
                    />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">
                        Element Types
                    </label>
                    <MultiSelect 
                        v-model="layerForm.element_types" 
                        :options="elementTypes"
                        optionLabel="label"
                        optionValue="value"
                        class="w-full"
                        placeholder="Select element types"
                        display="chip"
                    />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">
                        Parent Layer
                    </label>
                    <Dropdown 
                        v-model="layerForm.parent_layer_id" 
                        :options="availableLayers"
                        optionLabel="label"
                        optionValue="value"
                        class="w-full"
                        placeholder="Select parent layer (optional)"
                        showClear
                    />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">
                        Related Layers
                    </label>
                    <MultiSelect 
                        v-model="layerForm.related_layers" 
                        :options="availableLayers"
                        optionLabel="label"
                        optionValue="value"
                        class="w-full"
                        placeholder="Select related layers"
                        display="chip"
                    />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">
                        Description
                    </label>
                    <Textarea 
                        v-model="layerForm.description" 
                        rows="3"
                        class="w-full"
                        placeholder="Layer description"
                    />
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">
                            Z-Index
                        </label>
                        <InputNumber 
                            v-model="layerForm.z_index" 
                            class="w-full"
                            :min="0"
                        />
                    </div>
                    
                    <div class="flex items-center pt-7">
                        <Checkbox 
                            v-model="layerForm.default_visible" 
                            inputId="visible"
                            :binary="true"
                        />
                        <label for="visible" class="ml-2 text-sm font-medium text-surface-700 dark:text-surface-300">
                            Visible by Default
                        </label>
                    </div>
                </div>
            </div>
            
            <template #footer>
                <Button 
                    label="Cancel" 
                    text 
                    @click="showPropertiesDialog = false"
                />
                <Button 
                    label="Update Properties" 
                    @click="updateLayerProperties"
                />
            </template>
        </Dialog>
    </IcasLayout>
</template>
