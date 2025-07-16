<?php

namespace PdSeoOptimizer\App\RestRoutes;

use PdSeoOptimizer\App\Services\OpenAiClient;
use WP_REST_Request;
use WP_Error;

class OpenAiSeoRoute
{
    public function register()
    {
        register_rest_route('pd-seo/v1', '/generate-meta', [
            'methods' => 'POST',
            'callback' => [$this, 'handleRequest'],
            'permission_callback' => fn () => current_user_can('edit_posts'),
        ]);
    }

    public function handleRequest(WP_REST_Request $request)
    {
        $content = $request->get_param('content');
        if (empty($content)) {
            return new WP_Error('no_content', 'Brak treści.');
        }

        try {
            $openai = new OpenAiClient();
            return $openai->generateMeta($content);
        } catch (\Throwable $e) {
            return new WP_Error('openai_error', $e->getMessage());
        }
    }
}
