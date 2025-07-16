<?php

namespace PdSeoOptimizer;

use PdSeoOptimizer\Services\OpenAiClient;

class Filters
{
    
    public function __construct()
    {
        if (is_plugin_active('seo-by-rank-math/rank-math.php')) {
            add_filter('bulk_actions-edit-post', [$this, 'addBulkActions']);
            add_filter('bulk_actions-edit-page', [$this, 'addBulkActions']);
            add_filter('handle_bulk_actions-edit-post', [$this, 'handleBulkActions'], 10, 3);
            add_filter('handle_bulk_actions-edit-page', [$this, 'handleBulkActions'], 10, 3);
        }
    }

    function addBulkActions($actions)
    {
        $actions['generate_meta'] = __('Generate Meta (OpenAI)', 'pd-seo-optimizer');
        return $actions;
    }

    function handleBulkActions($redirect_to, $action, $post_ids)
    {
        if ($action !== 'generate_meta') {
            return $redirect_to;
        }

        $openAiClient = new OpenAiClient();

        foreach ($post_ids as $post_id) {
            $content = get_post_field('post_content', $post_id);
            
            $response = $openAiClient->generateMeta($content);

            $titleClean = trim($response['title'], '"');
            $descriptionClean = trim($response['description'], '"');

            update_post_meta($post_id, 'rank_math_title', $titleClean);
            update_post_meta($post_id, 'rank_math_description', $descriptionClean);
        }

        return add_query_arg('generated_meta', count($post_ids), $redirect_to);
    }
}