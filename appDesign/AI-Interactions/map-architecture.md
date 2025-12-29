# ICAS Map Platform Architecture

## 1. Vision & Scope
The ICAS map experience must deliver a tiered spatial model that mirrors how operators think:

1. **Sites (blue bands in the mockups)** — e.g., Monroe County Jail. Each site owns the polygons, layers, and telemetry for a physical campus.
2. **Maps / Levels (orange bands)** — e.g., Ground, L1, L2, Utility. Each level references a base SVG/Canvas asset plus render directives (grid size, aspect ratio, snap rules).
3. **Layers (stacked content)** — base plan SVG at the bottom, followed by thematic overlays: rooms, sensors, cameras, officers, action buttons, etc. Layers decide which data streams render and how they look.
4. **Viewports (blue boxes)** — curated windows into a level for fast navigation. Every viewport can override layer visibility (e.g., Cameras only) and has its own default zoom/pan. A root viewport always reflects the entire canvas and provides the canonical layer configuration.

The UI must support pinch/scroll zoom, pan, smooth transitions between viewports, and real-time updates from sensors or officers.

## 2. Data Model (initial migrations)

| Table | Purpose | Key Columns |
| --- | --- | --- |
| `sites` | Anchor entity for facilities | `name`, `slug`, `timezone`, `metadata json`
| `maps` | One row per site level | `site_id`, `name`, `slug`, `floor_label`, `sequence`, `svg_asset_path`, `canvas_config json`, `is_active`
| `map_layers` | Declarative layers stacked on each map | `map_id`, `key`, `display_name`, `layer_type` (`static_svg`, `room`, `sensor`, `camera`, `officer`, `action`), `z_index`, `default_visible`, `style_preset json`, `data_source json`
| `layer_elements` | Individual geometries / markers per layer | `layer_id`, `element_type` (`polygon`, `polyline`, `point`, `icon`), `geometry json` (normalized coordinates), `payload json` (room metadata, sensor id, etc.), `state json` (online/offline, threat level)
| `map_viewports` | Named windows into a map | `map_id`, `name`, `slug`, `is_root`, `bounds json` (`x`, `y`, `width`, `height` in normalized space), `default_zoom`, `default_pan`, `layer_overrides json`, `notes`
| `viewport_history` (optional) | Audit / analytics of viewport usage | `viewport_id`, `user_id`, `entered_at`, `duration`

**Notes**
- `canvas_config` stores DPI, snap grid, rotation, and native width/height so zoom math stays accurate.
- `data_source` can reference external feeds (`sensors.status`, `devices.api_endpoint`) so layers remain declarative.
- `layer_overrides` is a key-value store `{ layer_key: visibility | style overrides }`. The root viewport sets the canonical defaults; children override selectively.

### Migration Outline
1. `create_sites_table`
2. `create_maps_table`
3. `create_map_layers_table`
4. `create_layer_elements_table`
5. `create_map_viewports_table`
6. (Optional) `create_viewport_history_table`

Each table gets standard timestamps + soft deletes for safe iteration.

## 3. Backend Controllers & Services

| Controller | Responsibility |
| --- | --- |
| `SiteMapController` | CRUD for sites and their child maps. Provides `index` (site list + thumbnail), `show` (selected site w/ maps, layers, root viewport). |
| `MapAssetController` | Handles uploads of SVG/Canvas assets, DXF-to-SVG conversion, and versioning. Enforces min spec (unit scaling, viewport alignment anchors). |
| `MapLayerController` | Manage layer metadata (name, type, z-index) and trigger rebuilds of cached sprites (e.g., room polygons). |
| `LayerElementController` | Bulk upsert of geometry payloads, linking to sensors/cameras/officers. Supports search (`?type=sensor&status=critical`). |
| `ViewportController` | Manage root + child viewports, compute bounds from geometry selection, persist layer overrides. |
| `ViewportStateController` (API) | Emits JSON snapshots for the front-end (user-specific viewport selection, zoom, layer toggles). |
| `RealtimeMapController` | Broadcast updates to sockets when layer elements change (e.g., sensor state). Lives in communications service but shares domain objects. |

### Service Classes
- `MapRendererService`: takes a map + layer selection and returns draw instructions (ordered list per layer). Reusable for exports (PDF, static PNG) and front-end hydration.
- `ViewportResolver`: merges root config + overrides into a runtime manifest, enforces fallback to root when configs conflict.
- `LayerDataAdapters`: translate domain entities (Sensors, Officers, Cameras) into layer elements; keeps map tables generic and reusable.

## 4. Front-end Component Plan (Vue / Inertia)

| Component | Description |
| --- | --- |
| `MapsDashboard.vue` (page) | Site picker, list of maps, quick stats (alerts per level). Uses SSR data from `SiteMapController@index`. |
| `MapWorkspace.vue` (page) | Main canvas experience. Accepts `site`, `map`, `layers`, `viewports`, `initialViewportId`. |
| `MapToolbar.vue` | Left rail w/ tools (select, measure, annotate), zoom controls, viewport dropdown. Emits events to `MapWorkspace`. |
| `ViewportNavigator.vue` | Shows all viewports (root + child) as cards/mini-previews; allows double-click to jump. Handles layer override badges. |
| `LayerTogglePanel.vue` | Tree of layers with visibility toggles, per-layer opacity slider, lock/solo controls. Reads from Pinia store. |
| `MapCanvas.vue` | Core canvas + SVG compositor. Responsibilities:
  - load base SVG
  - normalize coordinates to canvas size
  - manage zoom/pan via `@vueuse/gesture` or `panzoom`
  - render dynamic overlays using `<canvas>` for performance (Konva/Pixi) or inline SVG groups for crisp lines.
| `LayerRenderer/*.vue` | One component per layer type (e.g., `RoomsLayer`, `SensorsLayer`, `CamerasLayer`, `OfficersLayer`, `ActionButtonsLayer`). Each consumes standardized `elements` props and draws to MapCanvas slots. |
| `RealtimeBadge.vue` | Small indicator for elements receiving live updates. |
| `ViewportConfigDialog.vue` | Form for editing viewport bounds & layer overrides; integrates with zoomed selection box on canvas. |
| `MapLegend.vue` | Displays symbology per layer, honors viewport overrides. |
| `useMapStore.ts` (Pinia) | Holds current site/map, active viewport, zoom, pan, layer states, and subscriptions to Reverb channels. |

### Zoom & Pan Strategy
- Base canvas sized to root viewport aspect ratio.
- Use `panzoom` or `@vueuse/gesture` for smooth wheel + drag interactions.
- Maintain zoom in store so viewports can define `snapZoom`, `minZoom`, `maxZoom`.
- When switching viewports, animate to new `pan/zoom` via `requestAnimationFrame` for continuity.

### Layer Rendering
- **Base Layer**: inline `<svg>` injected directly for crisp architectural lines.
- **Dynamic Layers**: use `<canvas>` overlays for high-frequency updates (officers, sensors). Each layer renderer receives normalized coords and draws relative to the current transform.
- **Hit Testing**: MapCanvas exposes utilities for pointer-to-canvas coordinate conversion so components can handle clicks/hover.

## 5. Real-time & Data Refresh
- Subscribe to `map.{map_id}.layer.{layer_key}` channels via Reverb. Payloads carry `element_id`, `state delta`, and `version`.
- Layer store applies patches optimistically and falls back to REST re-sync on version mismatch.
- Viewports specify `refresh_interval` (ms) for polling-only layers (e.g., nightly occupancy).

## 6. DXF/SVG Asset Pipeline
1. Operators upload DXF or SVG via `MapAssetController`.
2. Background job converts DXF > normalized SVG, extracts metadata (native width/height, units) and writes to `maps.svg_asset_path`.
3. Optional post-process: automatically generate draft room polygons by grouping closed polylines; store them as new layer elements flagged `auto_generated`.

## 7. Root Viewport & Layer Inheritance
- Every map has exactly one `is_root = true` viewport. It stores the canonical bounds (entire canvas) and base layer visibilities.
- Child viewports copy root config on creation. Editors then adjust `bounds` and `layer_overrides`.
- Front-end always merges: `resolvedLayers = root.layers ⊕ viewport.overrides ⊕ user session toggles`.

## 8. Next Steps
1. Implement migrations + models outlined above.
2. Seed sample data (sites, maps, layers, elements) using the SVG assets from `appDesign/drawio` to validate scaling.
3. Scaffold Pinia store + MapWorkspace page; load root viewport and allow manual zoom/pan.
4. Build LayerToggle and ViewportNavigator to prove override flow.
5. Integrate Reverb mock channel to update a sensor marker in real time.

## 9. API & Routing Contracts

| HTTP Route | Controller@Action | Payload | Notes |
| --- | --- | --- | --- |
| `GET /map` | `SiteMapController@index` | — | Returns list of sites, each with lightweight map summaries + root viewport id. Powers directory/dropdown. |
| `GET /map/{site}/{map}` | `SiteMapController@show` | — | Returns full map manifest `{ map, layers, viewports, rootViewportId }`. Used by `MapWorkspace.vue`. |
| `POST /map/assets` | `MapAssetController@store` | `file`, `site_id`, `name` | Upload DXF/SVG, triggers convert job, returns new map record. |
| `PUT /map/{map}` | `SiteMapController@update` | map metadata | Allows renaming floors, toggling active flag. |
| `POST /map/{map}/layers` | `MapLayerController@store` | display + data_source json | Creates a declarative layer. Bulk import endpoint accepts arrays. |
| `POST /layer/{layer}/elements` | `LayerElementController@bulkUpsert` | `[ { geometry, payload, state } ]` | Upserts geometry used by front-end renderers. |
| `POST /map/{map}/viewports` | `ViewportController@store` | bounds, overrides | Creates viewport; when `is_root=true` enforce uniqueness. |
| `GET /api/maps/{map}/state` | `ViewportStateController@show` | optional `viewport_id` | Returns resolved runtime manifest used for hydration/poll fallback. |
| `POST /api/viewports/{viewport}/events` | `ViewportStateController@events` | `event`, `payload` | Used by UI to log usage or share context; writes to `viewport_history`. |

## 10. Runtime Data Flow
1. User navigates to `/map`. Inertia receives SSR payload with all sites + `initialSiteId`. Pinia store seeds with first site + its root viewport.
2. Selecting a map triggers `useMapStore.loadMap(mapId)` which calls `GET /map/{site}/{map}`.
3. Response contains:
  - `map`: metadata + canvas config
  - `layers`: ordered list w/ default visibilities
  - `viewports`: array with `is_root` marker and overrides
  - `layerElements`: grouped by `layer_key`
4. `MapWorkspace` hydrates `MapCanvas` with base SVG + normalizes coordinates using `canvas_config` (native width/height). Dynamic layers subscribe to store slices.
5. User toggles a viewport. Store merges `rootViewport.layers -> viewport.layerOverrides -> userSessionOverrides` and emits events so each `LayerRenderer` knows whether to display.
6. Real-time deltas arrive over Reverb and call `useMapStore.applyLayerPatch(layerKey, patch)`. Store updates underlying array; renderers react via props/computed watchers.
7. When user pans/zooms, store updates `currentTransform`. `ViewportNavigator` can capture current bounds and post to `ViewportController` to create a new saved viewport.

## 11. Development Milestones
1. **Persistence Layer** — create migrations/models + repository helpers.
2. **Seed & Fixtures** — build seeders that load the provided SVG and create sample rooms/sensors to unblock UI.
3. **API Layer** — implement controllers, policies, and form requests to secure CRUD operations.
4. **Front-end Framework** — wire Pinia store, MapWorkspace shell, toolbar, viewport navigator, and toggle panel.
5. **Rendering Engine** — build MapCanvas + first two layers (Rooms + Sensors) as reference implementations.
6. **Realtime Channel** — integrate Reverb stub to move one officer marker and ensure watchers fire.
7. **QA & Observability** — add feature tests (controllers), browser tests (Inertia responses), and logging around viewport resolutions.

This architecture document establishes the shared vocabulary (site/map/layer/viewport) and the contracts between Laravel + Vue so the upcoming build phase can proceed methodically.
