<?php

namespace App\Http\Controllers;

use App\Models\Map;
use App\Models\MapViewport;
use Illuminate\Http\Request;

class ViewportController extends Controller
{
    public function store(Request $request, Map $map)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string',
            'is_root' => 'boolean',
            'bounds' => 'required|array',
            'default_zoom' => 'numeric',
            'default_pan' => 'nullable|array',
            'layer_overrides' => 'nullable|array',
            'refresh_interval' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        if (($data['is_root'] ?? false) === true && $map->viewports()->where('is_root', true)->exists()) {
            return response()->json(['message' => 'Root viewport already exists.'], 422);
        }

        $viewport = $map->viewports()->create($data);

        return response()->json($viewport, 201);
    }

    public function update(Request $request, Map $map, MapViewport $viewport)
    {
        abort_unless($viewport->map_id === $map->id, 404);

        $data = $request->validate([
            'name' => 'sometimes|string',
            'slug' => 'sometimes|string',
            'bounds' => 'sometimes|array',
            'default_zoom' => 'sometimes|numeric',
            'default_pan' => 'sometimes|array',
            'layer_overrides' => 'sometimes|array',
            'refresh_interval' => 'sometimes|integer',
            'notes' => 'sometimes|string',
        ]);

        $viewport->update($data);

        return response()->json($viewport);
    }
}
