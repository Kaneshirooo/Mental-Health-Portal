<?php

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Illuminate\Support\Facades\Http;

// Mock enough of Laravel to run a standalone test if possible, or just use raw Curl/Guzzle
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['GEMINI_API_KEY'];
$apiUrl = 'https://commentanalyzer.googleapis.com/v1alpha1/comments:analyze?key=' . $apiKey;

echo "Testing Perspective API with key: " . substr($apiKey, 0, 8) . "...\n";

$data = [
    'comment' => ['text' => 'I am feeling very happy today and I love life!'],
    'languages' => ['en'],
    'requestedAttributes' => [
        'TOXICITY' => new \stdClass(),
    ]
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";

if ($httpCode === 200) {
    echo "\n✅ SUCCESS: Perspective API is active with your Gemini key!\n";
} else {
    echo "\n❌ FAILED: Error code $httpCode. You likely need to enable 'Perspective API' in Google Cloud Console.\n";
}
