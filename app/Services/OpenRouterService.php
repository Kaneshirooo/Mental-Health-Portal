<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterService
{
    protected string $apiKey;
    protected string $model;
    protected string $apiUrl = 'https://openrouter.ai/api/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.key');
        $this->model = config('services.openrouter.model', 'openai/gpt-4o');
    }

    /**
     * Generate a response from OpenRouter AI.
     */
    public function generateResponse(array $messages, string $systemInstruction = ''): string
    {
        if (empty($this->apiKey)) {
            Log::error('OpenRouter API key is missing.');
            return $this->getFallbackReply($messages);
        }

        try {
            $formattedMessages = [];
            
            if ($systemInstruction) {
                $formattedMessages[] = ['role' => 'system', 'content' => $systemInstruction];
            }

            foreach ($messages as $msg) {
                // Adapt Gemini format ['role' => 'model', 'parts' => [['text' => '...']]] 
                // or standard format ['role' => 'user', 'content' => '...']
                $role = $msg['role'] === 'model' ? 'assistant' : $msg['role'];
                $content = '';

                if (isset($msg['parts'][0]['text'])) {
                    $content = $msg['parts'][0]['text'];
                } elseif (isset($msg['content'])) {
                    $content = $msg['content'];
                }

                $formattedMessages[] = ['role' => $role, 'content' => $content];
            }

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => config('app.url'), // OpenRouter requirement
                    'X-Title' => config('app.name'),
                ])
                ->post($this->apiUrl, [
                    'model' => $this->model,
                    'messages' => $formattedMessages,
                    'temperature' => 0.7,
                ]);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'] ?? '';
            }

            Log::error("OpenRouter API Error: " . $response->status() . " | " . $response->body());
        } catch (\Throwable $e) {
            Log::error("OpenRouter API Exception: " . $e->getMessage());
        }

        return $this->getFallbackReply($messages);
    }

    /**
     * Single turn classification (like emotion detection).
     */
    public function generateSingleTurn(string $prompt): string
    {
        return $this->generateResponse([['role' => 'user', 'content' => $prompt]]);
    }

    /**
     * Basic fallback reply in case of API failure.
     */
    protected function getFallbackReply(array $messages): string
    {
        return "I'm sorry, I'm having trouble connecting to my brain right now. Please try again in a moment.";
    }
}
