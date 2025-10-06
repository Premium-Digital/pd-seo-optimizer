<?php

namespace PdSeoOptimizer\Services;

class MetaTitleAndDescriptionGenerator
{
    private OpenAiClient $openAiClient;

    public function __construct(OpenAiClient $openAiClient)
    {
        $this->openAiClient = $openAiClient;
    }

    public function generateForPosts(array $postIds): void
    {
        foreach ($postIds as $postId) {
            $content = get_post_field('post_content', $postId);
            $response = $this->openAiClient->generateMeta($content);

            $titleClean = trim($response['title'], '"');
            $descriptionClean = trim($response['description'], '"');

            update_post_meta($postId, 'rank_math_title', $titleClean);
            update_post_meta($postId, 'rank_math_description', $descriptionClean);

            \PdSeoOptimizer\Logger::getInstance()->addLog($postId, "post", 'update', [
                'title' => $titleClean,
                'description' => $descriptionClean,
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
            $response = $this->openAiClient->generateMeta($content);

            $titleClean = trim($response['title'], '"');
            $descriptionClean = trim($response['description'], '"');

            update_term_meta($termId, 'rank_math_title', $titleClean);
            update_term_meta($termId, 'rank_math_description', $descriptionClean);

            \PdSeoOptimizer\Logger::getInstance()->addLog($termId, "term", 'update-term', [
                'title' => $titleClean,
                'description' => $descriptionClean,
            ]);
        }
    }
}
