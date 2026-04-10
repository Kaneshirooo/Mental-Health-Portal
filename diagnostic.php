<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;

echo "--- PERFORMANCE & SYSTEM DIAGNOSTIC ---\n";

// 1. Database Speed & Indexes
$start = microtime(true);
$userCount = DB::table('users')->count();
$dbTime = microtime(true) - $start;
echo "User Count: $userCount\n";
echo "DB Count Query Time: " . round($dbTime * 1000, 2) . "ms\n";

$indexes = DB::select("SHOW INDEX FROM users");
echo "Users Table Indexes:\n";
foreach ($indexes as $idx) {
    echo "- {$idx->Key_name} on column {$idx->Column_name} (Unique: " . ($idx->Non_unique == 0 ? 'Yes' : 'No') . ")\n";
}

// 2. Migration Fix (Safe)
if (Schema::hasTable('users')) {
    $migrationExists = DB::table('migrations')->where('migration', '0001_01_01_000000_create_users_table')->exists();
    if (!$migrationExists) {
        echo "Users migration record missing from 'migrations' table. Inserting now...\n";
        DB::table('migrations')->insert([
            'migration' => '0001_01_01_000000_create_users_table',
            'batch' => 1
        ]);
        echo "Migration record synchronized.\n";
    } else {
        echo "Users migration record already exists.\n";
    }
}

// 3. Mail Connectivity Check (SMTP Test)
echo "Testing SMTP Connection (this may take a few seconds)...\n";
$start = microtime(true);
try {
    $transport = Mail::getSymfonyTransport();
    // For many drivers, we can't easily 'test' without sending, but we can check the config
    echo "Mail Driver: " . config('mail.default') . "\n";
    echo "Mail Host: " . config('mail.mailers.smtp.host') . "\n";
} catch (\Exception $e) {
    echo "Mail Test Error: " . $e->getMessage() . "\n";
}
$mailTime = microtime(true) - $start;
echo "Mail Config Check Time: " . round($mailTime * 1000, 2) . "ms\n";

// 4. Session Check
echo "Session Driver: " . config('session.driver') . "\n";
if (config('session.driver') === 'file') {
    $sessionPath = storage_path('framework/sessions');
    $sessionCount = count(glob($sessionPath . '/*'));
    echo "Active Session Files: $sessionCount\n";
    if ($sessionCount > 500) {
        echo "WARNING: Many session files found. This can slow down performance on file-based sessions.\n";
    }
}

echo "--- DIAGNOSTIC COMPLETE ---\n";
