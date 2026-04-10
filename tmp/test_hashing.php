<?php

use Illuminate\Support\Facades\Hash;
use App\Models\User;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$password = 'Secret123!';
$hashedOnce = Hash::make($password);

$user = new User();
$user->password = $hashedOnce;

echo "Original Password: " . $password . "\n";
echo "Hashed Once: " . $hashedOnce . "\n";
echo "User Model Password: " . $user->password . "\n";

if (Hash::check($password, $user->password)) {
    echo "Check Passed!\n";
} else {
    echo "Check Failed! (Double hashing likely)\n";
    if (Hash::check($hashedOnce, $user->password)) {
        echo "Confirmed: Check passes against the FIRST hash, meaning it was hashed again.\n";
    }
}
