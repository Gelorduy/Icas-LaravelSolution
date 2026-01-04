<?php

namespace App\Http\Controllers;

use App\Models\LayerElement;
use App\Models\MapLayer;
use Illuminate\Http\Request;

class LayerElementController extends Controller
{
    public function index(MapLayer $layer)
    {
        return response()->json($layer->elements);
    }

    public function bulkUpsert(Request $request, MapLayer $layer)
    {
        $validated = $request->validate([
            'elements' => 'required|array',
            'elements.*.element_type' => 'required|string',
            'elements.*.geometry' => 'required|array',
            'elements.*.payload' => 'nullable|array',
            'elements.*.state' => 'nullable|array',
        ]);

        // Delete all existing elements for this layer
        $layer->elements()->delete();

        // Create new elements
        $elements = collect($validated['elements'])->map(function ($elementData) use ($layer) {
            return $layer->elements()->create([
                'element_type' => $elementData['element_type'],
                'geometry' => $elementData['geometry'],
                'payload' => $elementData['payload'] ?? null,
                'state' => $elementData['state'] ?? null,
            ]);
        });

        return response()->json([
            'message' => 'Elements saved successfully',
            'elements' => $elements
        ]);
    }
}
