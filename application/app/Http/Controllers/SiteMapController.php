<?php

namespace App\Http\Controllers;

use App\Models\Map;
use App\Models\Site;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SiteMapController extends Controller
{
    public function index()
    {
        $sites = Site::with(['maps' => function ($query) {
            $query->with('rootViewport')->orderBy('sequence');
        }])->orderBy('name')->get();

        return Inertia::render('Map/Index', [
            'sites' => $sites,
        ]);
    }

    public function show(Site $site, Map $map)
    {
        abort_unless($map->site_id === $site->id, 404);

        $map->load([
            'site',
            'layers' => function ($query) {
                $query->with('elements');
            },
            'viewports',
            'rootViewport',
        ]);

        return response()->json([
            'map' => $map,
            'layers' => $map->layers,
            'viewports' => $map->viewports,
            'rootViewportId' => optional($map->rootViewport)->id,
        ]);
    }
}
