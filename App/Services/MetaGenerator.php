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

    /**
     * Try to fetch and extract textual content from the public post URL.
     * Returns an empty string on failure.
     */
    private function fetchPostUrlContent(int $postId): string
    {
        $permalink = get_permalink($postId);
        if (empty($permalink)) {
            return '';
        }

        $response = wp_remote_get($permalink, [
            'timeout' => 10,
            'headers' => [
                'User-Agent' => 'PdSeoOptimizer/1.0',
            ],
        ]);

        if (is_wp_error($response)) {
            return '';
        }

        $code = wp_remote_retrieve_response_code($response);
        if ((int) $code !== 200) {
            return '';
        }

        $body = wp_remote_retrieve_body($response);
        if (empty($body)) {
            return '';
        }

        $text = wp_strip_all_tags($body);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if (function_exists('mb_substr')) {
            $text = mb_substr($text, 0, 5000);
        } else {
            $text = substr($text, 0, 5000);
        }

        return (string) $text;
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
                    }
                }
            }

            if (empty(trim($content))) {
                $fetched = $this->fetchPostUrlContent($postId);
                if (!empty($fetched)) {
                    $content = $fetched;
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
                    }
                }
            }
            // URL fallback when still empty
            if (empty(trim($content))) {
                $fetched = $this->fetchPostUrlContent($postId);
                if (!empty($fetched)) {
                    $content = $fetched;
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
                    }
                }
            }
            // URL fallback when still empty
            if (empty(trim($content))) {
                $fetched = $this->fetchPostUrlContent($postId);
                if (!empty($fetched)) {
                    $content = $fetched;
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
