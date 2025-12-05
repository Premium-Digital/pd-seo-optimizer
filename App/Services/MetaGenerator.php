<?php

namespace PdSeoOptimizer\Services;

class MetaGenerator
{
    private OpenAiClient $openAiClient;

    public function __construct(OpenAiClient $openAiClient)
    {
        $this->openAiClient = $openAiClient;
    }

    public function generateTitle(string $content): string
    {
        $response = $this->openAiClient->chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Wygeneruj wyłącznie meta tytuł (do 60 znaków) na podstawie treści posta. Nie dodawaj etykiet, wyjaśnień ani niczego innego. Zwróć tylko sam tytuł w jednej linii.',
                ],
                ['role' => 'user', 'content' => $content],
            ],
        ]);

        return trim($response->choices[0]->message->content ?? '', '"');
    }

    public function generateDescription(string $content): string
    {
        $response = $this->openAiClient->chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Wygeneruj wyłącznie meta opis (do 155 znaków) na podstawie treści posta. Opis ma być marketingowy, konkretny, zawierający główne słowo kluczowe. Nie dodawaj etykiet, nagłówków ani dodatkowego formatowania. Zwróć tylko opis.',
                ],
                ['role' => 'user', 'content' => $content],
            ],
        ]);

        return trim($response->choices[0]->message->content ?? '', '"');
    }
    
    public function generateMetaTitleAndDescription(string $content): array
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
            'title' => trim($title, '"'),
            'description' => trim($description, '"'),
        ];
    }

    public function generateMetaForPosts(array $postIds): void
    {
        foreach ($postIds as $postId) {
            $post = get_post($postId);
            $content = get_post_field('post_content', $postId);

            if ($post && $post->post_type === 'product' && empty(trim($content))) {
                if (function_exists('wc_get_product')) {
                    $product = wc_get_product($postId);
                    if ($product && method_exists($product, 'get_short_description')) {
                        $content = $product->get_short_description();
                    } else {
                        $content = get_post_field('post_excerpt', $postId);
                    }
                } else {
                    $content = get_post_field('post_excerpt', $postId);
                }
            }

            $response = $this->generateMetaTitleAndDescription($content);

            update_post_meta($postId, 'rank_math_title', $response['title']);
            update_post_meta($postId, 'rank_math_description', $response['description']);

            \PdSeoOptimizer\Logger::getInstance()->addLog($postId, "post", 'update', [
                'title' => $response['title'],
                'description' => $response['description']   ,
            ]);
        }
    }

    public function generateTitlesForPosts(array $postIds): void
    {
        foreach ($postIds as $postId) {
            $post = get_post($postId);
            $content = get_post_field('post_content', $postId);

            if ($post && $post->post_type === 'product' && empty(trim($content))) {
                if (function_exists('wc_get_product')) {
                    $product = wc_get_product($postId);
                    if ($product && method_exists($product, 'get_short_description')) {
                        $content = $product->get_short_description();
                    } else {
                        $content = get_post_field('post_excerpt', $postId);
                    }
                } else {
                    $content = get_post_field('post_excerpt', $postId);
                }
            }
            $title = $this->generateTitle($content);

            update_post_meta($postId, 'rank_math_title', $title);
            \PdSeoOptimizer\Logger::getInstance()->addLog($postId, "post", 'update-title', [
                'title' => $title,
            ]);
        }
    }

    public function generateDescriptionsForPosts(array $postIds): void
    {
        foreach ($postIds as $postId) {
            $post = get_post($postId);
            $content = get_post_field('post_content', $postId);

            if ($post && $post->post_type === 'product' && empty(trim($content))) {
                if (function_exists('wc_get_product')) {
                    $product = wc_get_product($postId);
                    if ($product && method_exists($product, 'get_short_description')) {
                        $content = $product->get_short_description();
                    } else {
                        $content = get_post_field('post_excerpt', $postId);
                    }
                } else {
                    $content = get_post_field('post_excerpt', $postId);
                }
            }
            $description = $this->generateDescription($content);

            update_post_meta($postId, 'rank_math_description', $description);

            \PdSeoOptimizer\Logger::getInstance()->addLog($postId, "post", 'update-description', [
                'description' => $description,
            ]);
        }
    }

    public function generateForTerms(array $termIds): void
    {
        foreach ($termIds as $termId) {
            $term = get_term($termId);
            if (!$term || is_wp_error($term)) {
                continue;
            }

            $content = $term->name . "\n\n" . $term->description;
            $response = $this->generateMetaTitleAndDescription($content);

            update_term_meta($termId, 'rank_math_title', $response['title']);
            update_term_meta($termId, 'rank_math_description', $response['description']);

            \PdSeoOptimizer\Logger::getInstance()->addLog($termId, "term", 'update-term', [
                'title' => $response['title'],
                'description' => $response['description'],
            ]);
        }
    }
}
