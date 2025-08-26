<?php

namespace PdSeoOptimizer;

class Actions
{
    public function __construct()
    {
        add_action( 'wp_enqueue_scripts', array( $this, 'registerStylesAndScripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'registerAdminStylesAndScripts' ));
        add_action( 'wp_ajax_pd_generate_meta_batch', array($this, 'handleGenerateMetaBatch'));
        add_action( 'wp_ajax_pd_generate_meta_terms_batch', [$this, 'handleGenerateMetaTermsBatch'] );
        add_action( 'admin_footer-edit.php', [$this, 'renderMetaGeneratorPopup']);
        add_action( 'admin_footer-edit-tags.php',  [$this, 'renderMetaGeneratorPopup'] );
    }

    public function registerStylesAndScripts()
    {
        //styles
        wp_enqueue_style( 'pd-seo-optimizer-styles', PD_SEO_OPTIMIZER_PLUGIN_DIR_URL . 'dist/front.css' );

        //scripts
        wp_enqueue_script( 'pd-seo-optimizer-scripts', PD_SEO_OPTIMIZER_PLUGIN_DIR_URL . 'dist/front.js', array(), null, true );
    }

    public function registerAdminStylesAndScripts()
    {
        //styles
        wp_enqueue_style( 'pd-seo-optimizer-admin-styles', PD_SEO_OPTIMIZER_PLUGIN_DIR_URL . 'dist/admin.css' );

        //scripts
        wp_enqueue_script('jquery');
        wp_enqueue_media();
        wp_enqueue_script( 'pd-seo-optimizer-admin-scripts', PD_SEO_OPTIMIZER_PLUGIN_DIR_URL . 'dist/admin.js', array('jquery'), null, true );

        wp_localize_script('pd-seo-optimizer-admin-scripts', 'pdSeoMetaData', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pd_seo_meta_nonce'),
            'batchSize' => (int) get_option('pd_seo_optimizer_batch_size'),
        ]);

    }

    public function handleGenerateMetaBatch() {
        check_ajax_referer('pd_seo_meta_nonce', 'nonce');

        $postIds = json_decode(stripslashes($_POST['ids']), true);
        if (!is_array($postIds)) {
            wp_send_json_error('Invalid post IDs');
        }

        $openAiClient = new \PdSeoOptimizer\Services\OpenAiClient();

        foreach ($postIds as $postId) {
            $content = get_post_field('post_content', $postId);
            $response = $openAiClient->generateMeta($content);

            $titleClean = trim($response['title'], '"');
            $descriptionClean = trim($response['description'], '"');

            update_post_meta($postId, 'rank_math_title', $titleClean);
            update_post_meta($postId, 'rank_math_description', $descriptionClean);

            \PdSeoOptimizer\Logger::getInstance()->addLog($postId, "post", 'update', [
                'title' => $titleClean,
                'description' => $descriptionClean,
            ]);
        }

        wp_send_json_success('Batch processed');
    }

    public function handleGenerateMetaTermsBatch() {
    check_ajax_referer('pd_seo_meta_nonce', 'nonce');

    $termIds = json_decode(stripslashes($_POST['ids']), true);
    if (!is_array($termIds)) {
        wp_send_json_error('Invalid term IDs');
    }

    $openAiClient = new \PdSeoOptimizer\Services\OpenAiClient();

    foreach ($termIds as $termId) {
        $term = get_term($termId);
        if (!$term || is_wp_error($term)) {
            continue;
        }

        // Treść do podania do AI – np. nazwa i opis kategorii
        $content = $term->name . "\n\n" . $term->description;

        $response = $openAiClient->generateMeta($content);

        $titleClean = trim($response['title'], '"');
        $descriptionClean = trim($response['description'], '"');

        // Rank Math zapisuje meta terminów w opcjach termmeta
        update_term_meta($termId, 'rank_math_title', $titleClean);
        update_term_meta($termId, 'rank_math_description', $descriptionClean);

        \PdSeoOptimizer\Logger::getInstance()->addLog($termId, "term", 'update-term', [
            'title' => $titleClean,
            'description' => $descriptionClean,
        ]);
    }

        wp_send_json_success('Batch processed (terms)');
    }

    public function renderMetaGeneratorPopup() {
        global $pagenow;
        $context = null;

        if ( $pagenow === 'edit.php' ) {
            $currentPostType  = $_GET['post_type'] ?? 'post';
            $allowedTypes     = \RankMath\Helper::get_allowed_post_types();
            if ( in_array($currentPostType, $allowedTypes, true) ) {
                $context = 'post';
            }
        }

        if ( $pagenow === 'edit-tags.php' ) {
            $currentTaxonomy   = $_GET['taxonomy'] ?? '';
            $allowedTaxonomies = \RankMath\Helper::get_allowed_taxonomies();
            $allowedTaxonomies = array_unique(array_merge($allowedTaxonomies, ['product_cat']));
            if ( in_array($currentTaxonomy, $allowedTaxonomies, true) ) {
                $context = 'term';
            }
        }

        if ( $context ) {
            include PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH . 'templates/admin/meta-generator-popup.php';
        }
    }
}