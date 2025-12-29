<?php

return [
    'storage_disk' => env('MAP_STORAGE_DISK', 'public'),
    'dxf_upload_path' => 'maps/uploads',
    'svg_output_path' => 'maps/renders',
    'converter_command' => env('DXF_TO_SVG_COMMAND', 'python3 ' . base_path('scripts/dxf_to_svg.py')),
];
