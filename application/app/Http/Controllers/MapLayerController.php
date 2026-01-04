<?php

namespace App\Http\Controllers;

use App\Models\Map;
use App\Models\MapLayer;
use Illuminate\Http\Request;

class MapLayerController extends Controller
{
    public function store(Request $request, Map $map)
    {
        $data = $request->validate([
            'key' => 'required|string',
            'display_name' => 'required|string',
            'layer_type' => 'required|string',
            'z_index' => 'nullable|integer',
            'default_visible' => 'boolean',
            'style_preset' => 'array',
            'data_source' => 'array',
            'parent_layer_id' => 'nullable|exists:map_layers,id',
            'element_types' => 'nullable|array',
            'related_layers' => 'nullable|array',
        ]);

        // Ensure unique key for this map
        $data['key'] = $data['key'] ?? \Illuminate\Support\Str::slug($data['display_name']);

        $layer = $map->layers()->create($data);

        return response()->json($layer, 201);
    }

    public function update(Request $request, Map $map, MapLayer $layer)
    {
        abort_unless($layer->map_id === $map->id, 404);

        $data = $request->validate([
            'display_name' => 'sometimes|string',
            'layer_type' => 'sometimes|string',
            'z_index' => 'sometimes|integer',
            'default_visible' => 'sometimes|boolean',
            'style_preset' => 'sometimes|array',
            'data_source' => 'sometimes|array',
            'parent_layer_id' => 'nullable|exists:map_layers,id',
            'element_types' => 'sometimes|array',
            'related_layers' => 'sometimes|array',
        ]);

        $layer->update($data);

        return response()->json($layer);
    }

    public function destroy(Map $map, MapLayer $layer)
    {
        abort_unless($layer->map_id === $map->id, 404);

        $layer->delete();

        return response()->json(['message' => 'Layer deleted successfully']);
    }

    public function toggleVisibility(Request $request, Map $map, MapLayer $layer)
    {
        abort_unless($layer->map_id === $map->id, 404);

        $visible = $request->boolean('visible');
        
        $layer->update(['default_visible' => $visible]);

        return response()->json(['visible' => $visible]);
    }
}
