import { defineStore } from 'pinia';
import axios from 'axios';

const layerIdentifier = (layer) => layer?.key ?? `layer-${layer?.id}`;

const baseDimensions = (manifest) => {
  const config = manifest?.map?.canvas_config ?? {};
  return {
    width: Number(config.width) || 1920,
    height: Number(config.height) || 1080,
  };
};

export const useMapStore = defineStore('map-store', {
  state: () => ({
    sites: [],
    selectedSiteId: null,
    selectedMapId: null,
    manifest: null,
    loading: false,
    error: null,
    zoom: 1,
    pan: { x: 0, y: 0 },
    activeViewportId: null,
    layerVisibility: {},
    userLayerOverrides: {},
  }),
  getters: {
    selectedSite(state) {
      return state.sites.find((site) => site.id === state.selectedSiteId) ?? null;
    },
    selectedMap(state) {
      const site = state.sites.find((s) => s.id === state.selectedSiteId);
      if (!site) {
        return null;
      }
      return site.maps?.find((map) => map.id === state.selectedMapId) ?? null;
    },
    orderedLayers(state) {
      return state.manifest?.layers ?? [];
    },
    activeViewport(state) {
      return state.manifest?.viewports?.find((viewport) => viewport.id === state.activeViewportId) ?? null;
    },
    canvasDimensions(state) {
      return baseDimensions(state.manifest);
    },
    zoomLevel(state) {
      const base = baseDimensions(state.manifest);
      const scale = state.zoom ?? 1;
      return {
        width: base.width / scale,
        height: base.height / scale,
        scale,
        pan: state.pan,
        base,
      };
    },
    visibleLayers(state) {
      const layers = state.manifest?.layers ?? [];
      const visibility = state.layerVisibility ?? {};

      return [...layers]
        .sort((a, b) => (a.z_index ?? 0) - (b.z_index ?? 0))
        .filter((layer) => {
          const key = layerIdentifier(layer);
          const explicit = visibility[key];
          if (typeof explicit === 'boolean') {
            return explicit;
          }
          return layer.default_visible ?? true;
        });
    },
  },
  actions: {
    initializeSites(sites = []) {
      this.sites = sites;
      if (!sites.length) {
        this.selectedSiteId = null;
        this.selectedMapId = null;
        this.manifest = null;
        return;
      }

      if (!this.selectedSiteId || !sites.some((site) => site.id === this.selectedSiteId)) {
        this.selectedSiteId = sites[0]?.id ?? null;
      }

      const currentSite = sites.find((site) => site.id === this.selectedSiteId);
      if (!currentSite) {
        this.selectedMapId = null;
        this.manifest = null;
        return;
      }

      if (!this.selectedMapId || !currentSite.maps?.some((map) => map.id === this.selectedMapId)) {
        this.selectedMapId = currentSite.maps?.[0]?.id ?? null;
      }

      if (this.selectedSiteId && this.selectedMapId) {
        this.loadMap(this.selectedSiteId, this.selectedMapId);
      }
    },
    selectSite(siteId) {
      if (this.selectedSiteId === siteId) {
        return;
      }
      this.selectedSiteId = siteId;
      const site = this.sites.find((s) => s.id === siteId);
      this.selectedMapId = site?.maps?.[0]?.id ?? null;
      if (this.selectedMapId) {
        this.loadMap(siteId, this.selectedMapId);
      } else {
        this.manifest = null;
      }
    },
    selectMap(mapId) {
      if (this.selectedMapId === mapId) {
        return;
      }
      this.selectedMapId = mapId;
      if (this.selectedSiteId && mapId) {
        this.loadMap(this.selectedSiteId, mapId);
      }
    },
    async loadMap(siteId, mapId) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await axios.get(route('api.maps.show', { site: siteId, map: mapId }));
        this.manifest = data;
        this.activeViewportId = data.rootViewportId ?? data.viewports?.[0]?.id ?? null;
        this.userLayerOverrides = {};
        this.applyLayerVisibility();

        const viewport = this.activeViewport;
        this.zoom = viewport?.default_zoom ?? 1;
        this.pan = viewport?.default_pan ?? { x: 0, y: 0 };
      } catch (error) {
        console.error(error);
        this.error = 'Unable to load map data.';
      } finally {
        this.loading = false;
      }
    },
    applyLayerVisibility() {
      const baseVisibility = {};
      (this.manifest?.layers ?? []).forEach((layer) => {
        const key = layerIdentifier(layer);
        baseVisibility[key] = layer.default_visible ?? true;
      });

      const viewport = this.manifest?.viewports?.find((vp) => vp.id === this.activeViewportId);
      if (viewport?.layer_overrides) {
        Object.entries(viewport.layer_overrides).forEach(([key, value]) => {
          baseVisibility[key] = value;
        });
      }

      this.layerVisibility = { ...baseVisibility, ...this.userLayerOverrides };
    },
    setActiveViewport(viewportId) {
      if (viewportId === this.activeViewportId) {
        return;
      }
      this.activeViewportId = viewportId;
      this.userLayerOverrides = {};
      const viewport = this.manifest?.viewports?.find((vp) => vp.id === viewportId);
      if (viewport) {
        this.zoom = viewport.default_zoom ?? this.zoom;
        this.pan = viewport.default_pan ?? this.pan;
      }
      this.applyLayerVisibility();
    },
    toggleLayerVisibility(layerOrKey) {
      const key = typeof layerOrKey === 'string' ? layerOrKey : layerIdentifier(layerOrKey);
      const current = this.layerVisibility[key] ?? true;
      const next = !current;
      this.userLayerOverrides = { ...this.userLayerOverrides, [key]: next };
      this.layerVisibility = { ...this.layerVisibility, [key]: next };
    },
    adjustZoom(delta) {
      const next = Math.min(5, Math.max(0.2, (this.zoom ?? 1) + delta));
      this.setZoom(next);
    },
    setZoom(scale) {
      const next = Math.min(5, Math.max(0.2, scale));
      this.zoom = Number(next.toFixed(2));
    },
    setPan(pan) {
      this.pan = pan;
    },
    upsertMap(siteId, mapData = {}) {
      if (!siteId || !mapData?.id) {
        return;
      }

      const site = this.sites.find((s) => s.id === siteId);
      if (!site) {
        return;
      }

      if (!Array.isArray(site.maps)) {
        site.maps = [];
      }

      const index = site.maps.findIndex((map) => map.id === mapData.id);
      if (index >= 0) {
        site.maps[index] = { ...site.maps[index], ...mapData };
      } else {
        site.maps.push(mapData);
      }

      // trigger reactivity on nested collection updates
      this.sites = this.sites.map((s) => (s.id === siteId ? { ...s, maps: [...site.maps] } : s));
    },
  },
});
