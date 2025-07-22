<?php

namespace PdSeoOptimizer;

class Actions
{
    public function __construct()
    {
        add_action( 'wp_enqueue_scripts', array( $this, 'registerStylesAndScripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'registerAdminStylesAndScripts' ));
        add_action( 'wp_ajax_pd_generate_meta_batch', array($this, 'handleGenerateMetaBatch'));
        add_action( 'admin_footer-edit.php', [$this, 'renderMetaGeneratorPopup']);
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

        $postIds = json_decode(stripslashes($_POST['post_ids']), true);
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

            \PdSeoOptimizer\Logger::getInstance()->addLog($postId, 'update', [
                'title' => $titleClean,
                'description' => $descriptionClean,
            ]);
        }

        wp_send_json_success('Batch processed');
    }  

    public function renderMetaGeneratorPopup() {
        $currentPostType = $_GET['post_type'] ?? 'post';
        $allowedTypes = \RankMath\Helper::get_allowed_post_types();

        if (!in_array($currentPostType, $allowedTypes, true)) {
            return;
        }
        include PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH . 'templates/admin/meta-generator-popup.php';
    }
}