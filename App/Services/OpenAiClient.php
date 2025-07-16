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
        $response = $this->client->chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'system', 'content' => 'Na podstawie poniższej treści posta wygeneruj:
                    1. Meta title — maksymalnie 60 znaków, chwytliwy, zawierający główne słowo kluczowe i zachęcający do kliknięcia.
                    2. Meta description — maksymalnie 155 znaków, opisujący zwięźle temat posta, również z głównym słowem kluczowym, w formie marketingowej.'],
                ['role' => 'user', 'content' => $content],
            ],
        ]);

        $output = $response->choices[0]->message->content ?? '';

        preg_match('/title:(.+?)\n/i', $output, $title);
        preg_match('/description:(.+)/i', $output, $description);

        return [
            'title' => trim($title[1] ?? ''),
            'description' => trim($description[1] ?? ''),
        ];
    }
}
