<?php

namespace App\Http\Controllers;

use App\Jobs\ConvertDxfToSvg;
use App\Models\Map;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class MapImportController extends Controller
{
    public function store(Request $request, Site $site)
    {
        if (! $request->user()) {
            abort(403);
        }

        $request->validate([
            'blueprint' => ['required', 'file', 'max:51200'],
            'filename' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $request->file('blueprint');
        $extension = strtolower($file->getClientOriginalExtension());
        
        // Validate file extension
        if (!in_array($extension, ['dxf', 'dfx', 'svg'])) {
            throw ValidationException::withMessages([
                'blueprint' => 'The file must be a DXF, DFX, or SVG file.',
            ]);
        }

        $validated = [
            'blueprint' => $file,
            'filename' => $request->input('filename'),
        ];

        /** @var UploadedFile $file */
        $file = $validated['blueprint'];
        $disk = config('maps.storage_disk', 'public');
        $filesystem = Storage::disk($disk);
        $extension = strtolower($file->getClientOriginalExtension());
        
        // Determine if this is an SVG or DXF file
        $isSvg = $extension === 'svg';
        
        if ($isSvg) {
            // For SVG files, store directly in the renders directory
            $renderDirectory = config('maps.svg_output_path', 'maps/renders');
            $filename = uniqid('map_', true) . '.svg';
            
            // Ensure directory exists
            if (!$filesystem->exists($renderDirectory)) {
                $filesystem->makeDirectory($renderDirectory, 0755, true);
            }
            
            $path = $file->storeAs($renderDirectory, $filename, $disk);
            
            if (! $path) {
                throw ValidationException::withMessages([
                    'blueprint' => 'Unable to store uploaded SVG file.',
                ]);
            }

            $map = Map::create([
                'site_id' => $site->id,
                'name' => $validated['filename'] ?? $file->getClientOriginalName() ?? 'Imported Map',
                'slug' => uniqid('map_', true),
                'svg_asset_path' => $filesystem->url($path),
                'source_dxf_path' => null,
                'conversion_status' => 'completed',
                'conversion_notes' => 'SVG imported directly without conversion',
                'is_active' => true,
            ]);
            
            // Create base layer for SVG import
            $this->createBaseLayer($map, $filesystem->url($path));
            
            return response()->json([
                'message' => 'SVG map imported successfully.',
                'map_id' => $map->id,
                'map' => $map->fresh(),
            ], 201);
        }
        
        // For DXF files, use the existing conversion process
        $path = $file->store(config('maps.dxf_upload_path', 'maps/uploads'), $disk);

        if (! $path) {
            throw ValidationException::withMessages([
                'blueprint' => 'Unable to store uploaded file.',
            ]);
        }

        $map = Map::create([
            'site_id' => $site->id,
            'name' => $validated['filename'] ?? $file->getClientOriginalName() ?? 'Imported Blueprint',
            'slug' => uniqid('map_', true),
            'svg_asset_path' => '',
            'source_dxf_path' => $path,
            'conversion_status' => 'queued',
            'conversion_notes' => null,
            'is_active' => false,
        ]);
        
        try {
            ConvertDxfToSvg::dispatchSync($map);
            $map->refresh();
        } catch (Throwable $exception) {
            report($exception);
        }

        return response()->json([
            'message' => 'Blueprint uploaded and processed.',
            'map_id' => $map->id,
            'map' => $map->fresh(),
        ], 201);
    }
    
    /**
     * Create a base floor plan layer for the imported map
     */
    private function createBaseLayer(Map $map, string $svgUrl): void
    {
        $map->layers()->create([
            'key' => 'floor-plan',
            'display_name' => 'Floor Plan',
            'layer_type' => 'svg_overlay',
            'z_index' => 0,
            'default_visible' => true,
            'style_preset' => [
                'group_label' => 'Base Layers',
                'description' => 'Building floor plan from imported file',
                'opacity' => 1,
            ],
            'data_source' => [
                'svg_path' => $svgUrl,
                'type' => 'svg_overlay',
            ],
        ]);
    }

    /**
     * Delete a map and all associated layers, viewports, and elements
     */
    public function destroy(Request $request, Site $site, Map $map)
    {
        if (! $request->user()) {
            abort(403);
        }

        // Verify map belongs to site
        if ($map->site_id !== $site->id) {
            abort(404);
        }

        // Delete associated layer elements first
        foreach ($map->layers as $layer) {
            $layer->elements()->delete();
        }

        // Delete layers
        $map->layers()->delete();

        // Delete viewports
        $map->viewports()->delete();

        // Delete the map itself
        $mapName = $map->name;
        $map->delete();

        return response()->json([
            'message' => "Map '{$mapName}' has been deleted successfully.",
        ], 200);
    }
}
