<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ActivityService
{
    /**
     * Fetch a random wellness-focused activity from the Bored API.
     */
    public function getRandomActivity(string $type = 'relaxation'): string
    {
        try {
            // Types available: education, recreational, social, diy, charity, cooking, relaxation, music, busywork
            $response = Http::timeout(5)->get('https://www.boredapi.com/api/activity', [
                'type' => $type
            ]);

            if ($response->successful()) {
                return $response->json()['activity'];
            }
        } catch (\Exception $e) {
            Log::error('Bored API Error: ' . $e->getMessage());
        }

        // Fallback activities if API fails
        $fallbacks = [
            'Take a 10-minute walk outside and notice 3 things you haven\'t seen before.',
            'Practice deep breathing: inhale for 4 counts, hold for 4, exhale for 8.',
            'Write down three things you are grateful for today.',
            'Listen to a single song and focus entirely on the lyrics and instruments.',
            'Do a quick 5-minute stretching routine.'
        ];

        return $fallbacks[array_rand($fallbacks)];
    }
}
