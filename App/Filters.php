<?php

namespace PdSeoOptimizer;

class Filters
{
    
    public function __construct()
    {
        if (is_plugin_active('seo-by-rank-math/rank-math.php')) {
            add_action('init', [$this, 'addBulkActionsForRankMathCpt'], 20, 2);
        }
    }

    public function addBulkActionsForRankMathCpt(){
        if (!class_exists('\RankMath\Helper')) {
            return;
        }

        $postTypes = \RankMath\Helper::get_allowed_post_types();

        if (!is_array($postTypes)) {
            return;
        }

        foreach ($postTypes as $postType) {
            add_filter("bulk_actions-edit-{$postType}", [$this, 'addBulkActions']);
            add_filter("handle_bulk_actions-edit-{$postType}", [$this, 'handleBulkActions'], 10, 3);
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

        return add_query_arg([
            'pd_generate_meta' => 1,
            'post_ids' => implode(',', $post_ids),
        ], $redirect_to);
    }
}