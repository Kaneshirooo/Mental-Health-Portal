<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = User::all();
echo "Total Users: " . $users->count() . "\n";

foreach ($users as $user) {
    echo "User ID: {$user->user_id}, Email: {$user->email}, Role: {$user->user_type->value}\n";
    if ($user->email !== strtolower($user->email)) {
        echo "  [!] Mixed Case Email Found: {$user->email}\n";
    }
}

// Check for duplicates if lowercased
$lowercased = $users->map(fn($u) => strtolower($u->email));
if ($lowercased->unique()->count() !== $users->count()) {
    echo "\n[WARNING] Duplicate emails found if lowercased!\n";
}
