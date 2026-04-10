<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "--- System Check ---\n";

// Check Tables
$tables = ['users', 'jobs', 'sessions', 'cache', 'login_attempts', 'session_logs'];
foreach ($tables as $table) {
    echo "Table '$table' exists: " . (Schema::hasTable($table) ? 'Yes' : 'No') . "\n";
}

// Check migration state
$pendingUsers = DB::table('migrations')->where('migration', '0001_01_01_000000_create_users_table')->exists();
echo "Users migration in table: " . ($pendingUsers ? 'Yes' : 'No') . "\n";

if (!$pendingUsers && Schema::hasTable('users')) {
    echo "Fixing users migration state...\n";
    DB::table('migrations')->insert([
        'migration' => '0001_01_01_000000_create_users_table',
        'batch' => 1
    ]);
    echo "Fixed!\n";
}

// Check session driver
echo "Session Driver: " . config('session.driver') . "\n";
echo "Mail Mailer: " . config('mail.default') . "\n";

echo "--- End Check ---\n";
