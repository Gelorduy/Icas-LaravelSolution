<?php

namespace App\Http\Controllers;

use App\Models\LayerElement;
use App\Models\MapLayer;
use Illuminate\Http\Request;

class LayerElementController extends Controller
{
    public function bulkUpsert(Request $request, MapLayer $layer)
    {
        $validated = $request->validate([
            'elements' => 'required|array',
            'elements.*.element_type' => 'required|string',
            'elements.*.geometry' => 'required|array',
            'elements.*.payload' => 'nullable|array',
            'elements.*.state' => 'nullable|array',
        ]);

        $elements = collect($validated['elements'])->map(function ($payload) use ($layer) {
            return $layer->elements()->updateOrCreate(
                [
                    'element_type' => $payload['element_type'],
                    'geometry' => $payload['geometry'],
                ],
                [
                    'payload' => $payload['payload'] ?? null,
                    'state' => $payload['state'] ?? null,
                ]
            );
        });

        return response()->json($elements);
    }
}
