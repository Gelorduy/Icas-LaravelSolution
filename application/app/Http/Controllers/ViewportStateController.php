<?php

namespace App\Http\Controllers;

use App\Models\Map;
use App\Models\MapViewport;
use Illuminate\Http\Request;

class ViewportStateController extends Controller
{
    public function show(Request $request, Map $map)
    {
        $viewportId = $request->query('viewport_id');
        $viewport = $viewportId ? MapViewport::findOrFail($viewportId) : $map->rootViewport;

        $map->load(['layers', 'viewports']);

        return response()->json([
            'map' => $map,
            'layers' => $map->layers,
            'viewports' => $map->viewports,
            'activeViewport' => $viewport,
        ]);
    }
}
