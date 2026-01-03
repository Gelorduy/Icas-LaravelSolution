<?php

namespace App\Jobs;

use App\Models\Map;
use App\Models\MapLayer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Throwable;

class ConvertDxfToSvg implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Map $map)
    {
    }

    public function handle(): void
    {
        $map = $this->map->fresh();
        if (! $map || ! $map->source_dxf_path) {
            return;
        }

        $disk = config('maps.storage_disk', 'public');
        $filesystem = Storage::disk($disk);
        $svgPath = config('maps.svg_output_path', 'maps/renders') . '/' . $map->id . '.svg';
        $absoluteDxf = $filesystem->path($map->source_dxf_path);
        $absoluteSvg = $filesystem->path($svgPath);

        $command = config('maps.converter_command');
        $process = Process::fromShellCommandline($command . ' ' . escapeshellarg($absoluteDxf) . ' ' . escapeshellarg($absoluteSvg));
        $process->setTimeout(120);

        try {
            $process->mustRun();
            $mirrorUrl = $this->mirrorSvgIntoUploadDirectory($filesystem, $map->source_dxf_path, $svgPath);
            $map->update([
                'svg_asset_path' => $filesystem->url($svgPath),
                'conversion_status' => 'completed',
                'conversion_notes' => $mirrorUrl ? 'SVG mirror saved to ' . $mirrorUrl : null,
                'is_active' => true,
            ]);
            
            // Create base floor plan layer if it doesn't exist
            $this->createBaseLayer($map, $filesystem->url($svgPath));
        } catch (Throwable $exception) {
            Log::error('DXF conversion failed', [
                'map_id' => $map->id,
                'message' => $exception->getMessage(),
                'output' => $process->getErrorOutput(),
            ]);

            $map->update([
                'conversion_status' => 'failed',
                'conversion_notes' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    private function mirrorSvgIntoUploadDirectory(FilesystemAdapter $filesystem, ?string $sourcePath, string $renderPath): ?string
    {
        if (! $sourcePath || ! $filesystem->exists($renderPath)) {
            return null;
        }

        $info = pathinfo($sourcePath);
        $directory = $info['dirname'] ?? '';
        $directory = ($directory && $directory !== '.') ? trim($directory, '/') . '/' : '';
        $filename = $info['filename'] ?? null;

        if (! $filename) {
            return null;
        }

        $targetPath = $directory . $filename . '.svg';

        try {
            if ($directory && ! $filesystem->exists($directory)) {
                $filesystem->makeDirectory($directory);
            }

            $filesystem->copy($renderPath, $targetPath);

            return $filesystem->url($targetPath);
        } catch (Throwable $exception) {
            Log::warning('Failed to mirror SVG into upload directory', [
                'map_id' => $this->map->id,
                'target' => $targetPath,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function createBaseLayer(Map $map, string $svgUrl): void
    {
        // Check if base layer already exists
        $existingLayer = MapLayer::where('map_id', $map->id)
            ->where('key', 'floor-plan')
            ->first();

        if ($existingLayer) {
            // Update existing layer with new SVG path
            $existingLayer->update([
                'data_source' => [
                    'svg_path' => $svgUrl,
                    'type' => 'svg_overlay',
                ],
            ]);
            return;
        }

        // Create new base layer
        MapLayer::create([
            'map_id' => $map->id,
            'key' => 'floor-plan',
            'display_name' => 'Floor Plan',
            'layer_type' => 'svg_overlay',
            'z_index' => 0,
            'default_visible' => true,
            'style_preset' => [
                'group_label' => 'Base Layers',
                'description' => 'Building floor plan from DXF import',
                'opacity' => 1,
            ],
            'data_source' => [
                'svg_path' => $svgUrl,
                'type' => 'svg_overlay',
            ],
        ]);
    }
}
