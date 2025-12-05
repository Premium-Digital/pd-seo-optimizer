<?php

namespace PdSeoOptimizer\Services;

use OpenAI;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;

class OpenAiClient
{
    private $client;

    public function __construct()
    {
        $apiKey = get_option('pd_seo_optimizer_openai_api_key');
        if (!$apiKey) {
            throw new \RuntimeException('Brakuje klucza OpenAI.');
        }

        $httpClient = new Psr18Client(
            HttpClient::create(['timeout' => 360])
        );

        $this->client = OpenAI::factory()
            ->withApiKey($apiKey)
            ->withHttpClient($httpClient)
            ->make();
    }

    public function chat()
    {
        return $this->client->chat();
    }

}
