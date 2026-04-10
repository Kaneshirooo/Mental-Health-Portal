<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PerspectiveService
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.perspective.key') ?? env('GEMINI_API_KEY');
        $this->apiUrl = 'https://commentanalyzer.googleapis.com/v1alpha1/comments:analyze';
    }

    /**
     * Analyze text for toxicity and other attributes.
     */
    public function analyzeText(string $text): array
    {
        if (empty($text)) {
            return ['is_safe' => true, 'scores' => []];
        }

        try {
            $response = Http::post($this->apiUrl . '?key=' . $this->apiKey, [
                'comment' => ['text' => $text],
                'languages' => ['en'],
                'requestedAttributes' => [
                    'TOXICITY' => new \stdClass(),
                    'SEVERE_TOXICITY' => new \stdClass(),
                    'IDENTITY_ATTACK' => new \stdClass(),
                    'INSULT' => new \stdClass(),
                    'PROFANITY' => new \stdClass(),
                    'THREAT' => new \stdClass(),
                ]
            ]);

            if ($response->successful()) {
                $scores = $response->json()['attributeScores'];
                
                // Return simplified safety check
                // We consider it "unsafe" if any attribute exceeds 0.7
                $isSafe = true;
                $results = [];

                foreach ($scores as $attr => $data) {
                    $score = $data['summaryScore']['value'];
                    $results[$attr] = $score;
                    if ($score > 0.70) {
                        $isSafe = false;
                    }
                }

                return [
                    'is_safe' => $isSafe,
                    'scores' => $results,
                ];
            }

            Log::warning('Perspective API call failed: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Perspective API Exception: ' . $e->getMessage());
        }

        // Default to safe if API fails (to not block the user)
        return ['is_safe' => true, 'scores' => [], 'error' => true];
    }
}
