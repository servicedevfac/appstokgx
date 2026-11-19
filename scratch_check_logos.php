<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (\App\Models\Entreprise::all() as $e) {
    echo "ID: {$e->id} | Nom: {$e->nom} | Logo: '{$e->logo}'\n";
    if ($e->logo) {
        echo "  public_path: " . public_path($e->logo) . " (exists: " . (file_exists(public_path($e->logo)) ? 'YES' : 'NO') . ")\n";
        echo "  storage_path: " . storage_path('app/public/' . $e->logo) . " (exists: " . (file_exists(storage_path('app/public/' . $e->logo)) ? 'YES' : 'NO') . ")\n";
    }
}
