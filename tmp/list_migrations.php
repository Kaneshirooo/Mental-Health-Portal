<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$migrations = DB::table('migrations')->pluck('migration')->toArray();
echo "--- Recorded Migrations ---\n";
foreach ($migrations as $m) {
    echo $m . "\n";
}
echo "--- End ---\n";
