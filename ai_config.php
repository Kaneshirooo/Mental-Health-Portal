<?php
// =====================================================================
// AI Configuration — Google Gemini API (used by Aria chat)
// =====================================================================
// To enable Gemini AI: get a key at https://aistudio.google.com/app/apikey
// and replace 'YOUR_GEMINI_API_KEY_HERE' with your actual key.
// Until then, Aria uses the built-in Smart Counselor (no key needed).
// NOTE: The previous key was a Groq key and is incompatible with Gemini.
// =====================================================================

define('GEMINI_API_KEY', 'AIzaSyCyyE1U8QqRlCgjSICWJKxu16uTpcJm_sk');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent');

/**
 * callGemini() — A shared helper to communicate with Google Gemini.
 * @param array $payload The JSON-serializable request body.
 * @return string|false The AI's response text, or false on failure.
 */
function callGemini($payload) {
    if (GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE') return false;

    $ch = curl_init(GEMINI_API_URL . '?key=' . GEMINI_API_KEY);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) return false;

    $data = json_decode($response, true);
    return $data['candidates'][0]['content']['parts'][0]['text'] ?? false;
}

