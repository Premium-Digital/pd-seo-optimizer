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

    public function generateAltFromImage(string $postTitle, string $imageUrl , string $imagePath): string
    {
        if (!file_exists($imagePath)) {
            throw new \RuntimeException("Plik {$imagePath} nie istnieje.");
        }

        $response = $this->client->chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Jesteś ekspertem SEO i generujesz ALT teksty dla obrazków. ALT musi być dokładnym i zwięzłym opisem tego, co znajduje się na obrazie, maks. 125 znaków, preferowane krótsze. Nie używaj słów typu "zdjęcie", "obrazek", "grafika".',
                ],
                ['role'=>'user', 'content'=>[
                   ['type'=>'text', 'text'=>"Tutuł posta: {$postTitle}\n Proszę wygeneruj ALT dla tego obrazu na podstawie tytułu posta i zawartości obrazu."],
                   ['type'=>'image_url', 'image_url'=>['url'=>"{$imageUrl}"] ],
                ],
            ],
            ],
        ]);

        return trim($response->choices[0]->message->content ?? '' );
    }

}
