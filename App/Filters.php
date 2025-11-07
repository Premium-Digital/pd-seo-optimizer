<?php

namespace PdSeoOptimizer;

class Filters
{
    
    public function __construct()
    {
        if (is_plugin_active('seo-by-rank-math/rank-math.php')) {
            add_action('init', [$this, 'addBulkActionsForRankMath'], 20, 2);
        }
    }

    public function addBulkActionsForRankMath(){
        if (!class_exists('\RankMath\Helper')) {
            return;
        }

        $postTypes = \RankMath\Helper::get_allowed_post_types();
        $taxonomies = \RankMath\Helper::get_allowed_taxonomies();

        $taxonomies = array_unique(array_merge($taxonomies, ['product_cat']));
        if (is_array($postTypes)) {
            foreach ($postTypes as $postType) {
                add_filter("bulk_actions-edit-{$postType}", [$this, 'addBulkActions']);
                add_filter("handle_bulk_actions-edit-{$postType}", [$this, 'handleBulkActions'], 10, 3);
            }
        }

        if (is_array($taxonomies)) {
            foreach ($taxonomies as $taxonomy) {
                add_filter("bulk_actions-edit-{$taxonomy}", [$this, 'addBulkActionsTerms']);
                add_filter("handle_bulk_actions-edit-{$taxonomy}", [$this, 'handleBulkActions'], 10, 3);
            }
        }
    }

    function addBulkActionsTerms($actions)
    {
        $actions['generate_meta_terms'] = __('Generate Meta (OpenAI)', 'pd-seo-optimizer');
        return $actions;
    }

    function addBulkActions($actions)
    {
        $actions['generate_meta'] = __('Generate Meta (OpenAI)', 'pd-seo-optimizer');
        $actions['generate_image_alts'] = __('Generate Image Alts (AI)', 'pd-seo-optimizer');
        return $actions;
    }

    function handleBulkActions($redirect_to, $action, $post_ids)
    {
        if ($action === 'generate_meta') {
            return add_query_arg([
                'pd_generate_meta' => 1,
                'ids' => implode(',', $post_ids),
            ], $redirect_to);
        }

        if ($action === 'generate_image_alts') {
            return add_query_arg([
                'pd_generate_image_alts' => 1,
                'ids' => implode(',', $post_ids),
            ], $redirect_to);
        }

        return $redirect_to;
    }
}