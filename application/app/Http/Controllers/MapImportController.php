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

        $validated = $request->validate([
            'blueprint' => [
                'required',
                'file',
                'mimes:dxf,dfx',
                'max:51200',
            ],
            'filename' => ['nullable', 'string', 'max:255'],
        ]);

        /** @var UploadedFile $file */
        $file = $validated['blueprint'];
        $disk = config('maps.storage_disk', 'public');
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
}
