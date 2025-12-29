<?php
require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
$site = \App\Models\Site::find(2);

$uploadedFile = new \Illuminate\Http\UploadedFile(
    __DIR__.'/storage/app/public/maps/uploads/test-floorplan.dxf',
    'floorplan.dxf',
    'application/dxf',
    null,
    true
);

$request = \Illuminate\Http\Request::create(
    '/api/sites/'.$site->id.'/maps/import-dxf',
    'POST',
    [
        'filename' => 'Simulated Map'
    ],
    [],
    [
        'blueprint' => $uploadedFile,
    ]
);
$request->setUserResolver(fn () => $user);

$controller = new \App\Http\Controllers\MapImportController();
$response = $controller->store($request, $site);

echo 'Status: '.$response->status().PHP_EOL;
echo $response->getContent().PHP_EOL;
