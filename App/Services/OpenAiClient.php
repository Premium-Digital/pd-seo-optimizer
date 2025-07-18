<?php

namespace PdSeoOptimizer\Services;

use OpenAI;

class OpenAiClient
{
    private $client;

    public function __construct()
    {
        $apiKey = get_option('pd_seo_optimizer_openai_api_key');
        if (!$apiKey) {
            throw new \RuntimeException('Brakuje klucza OpenAI.');
        }

        $this->client = OpenAI::client($apiKey);
    }

    public function generateMeta(string $content): array
    {
        if (empty($content)) {
            return [
                'title' => '',
                'description' => '',
            ];
        }

        $title = $this->generateTitle($content);
        $description = $this->generateDescription($content);
        
        return [
            'title' => $title,
            'description' => $description,
        ];
    }

    public function generateTitle(string $content): string
    {
        $response = $this->client->chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Wygeneruj wyłącznie meta tytuł (do 60 znaków) na podstawie treści posta. Nie dodawaj etykiet, wyjaśnień ani niczego innego. Zwróć tylko sam tytuł w jednej linii.',
                ],
                ['role' => 'user', 'content' => $content],
            ],
        ]);

        return trim($response->choices[0]->message->content ?? '');
    }

    public function generateDescription(string $content): string
    {
        $response = $this->client->chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Wygeneruj wyłącznie meta opis (do 155 znaków) na podstawie treści posta. Opis ma być marketingowy, konkretny, zawierający główne słowo kluczowe. Nie dodawaj etykiet, nagłówków ani dodatkowego formatowania. Zwróć tylko opis.',
                ],
                ['role' => 'user', 'content' => $content],
            ],
        ]);

        return trim($response->choices[0]->message->content ?? '');
    }
}
