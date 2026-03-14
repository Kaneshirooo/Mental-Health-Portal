<?php
// =====================================================================
// AI Configuration — Google Gemini API (used by Aria chat)
// =====================================================================
// To enable Gemini AI: get a key at https://aistudio.google.com/app/apikey
// and replace 'YOUR_GEMINI_API_KEY_HERE' with your actual key.
// Until then, Aria uses the built-in Smart Counselor (no key needed).
// NOTE: The previous key was a Groq key and is incompatible with Gemini.
// =====================================================================

define('GEMINI_API_KEY', 'AIzaSyBf-8CBa4nC9vU_ih1gEEzuqggi9Ac5J-8');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent');

