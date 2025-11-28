<?php

namespace PdSeoOptimizer;
use PdSeoOptimizer\Services\AltGenerator;
use PdSeoOptimizer\Services\OpenAiClient;
use PdSeoOptimizer\Services\MetaTitleAndDescriptionGenerator;

class Actions
{
    public function __construct()
    {
        add_action( 'wp_enqueue_scripts', array( $this, 'registerStylesAndScripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'registerAdminStylesAndScripts' ));
        add_action( 'wp_ajax_pd_generate_meta_batch', array($this, 'handleGenerateMetaBatch'));
        add_action( 'wp_ajax_pd_generate_meta_terms_batch', [$this, 'handleGenerateMetaTermsBatch'] );
        add_action( 'wp_ajax_pd_generate_image_alts_batch', [$this, 'handleGenerateImageAltsBatch']);
        add_action( 'wp_ajax_pd_generate_media_alts_batch', [$this, 'handleGenerateMediaAltsBatch']);
        add_action( 'wp_ajax_pd_generate_single_attachment_alt', [$this, 'handleGenerateSingleAttachmentAlt']);
        add_action( 'admin_footer-edit.php', [$this, 'renderMetaGeneratorPopup']);
        add_action( 'admin_footer-edit-tags.php',  [$this, 'renderMetaGeneratorPopup'] );
        add_action( 'admin_footer-upload.php', [$this, 'renderMetaGeneratorPopup'] );
    }

    public function registerStylesAndScripts()
    {
        //styles
        wp_enqueue_style( 'pd-seo-optimizer-styles', PD_SEO_OPTIMIZER_PLUGIN_DIR_URL . 'dist/pd-seo-optimizer-front.css' );

        //scripts
        wp_enqueue_script( 'pd-seo-optimizer-scripts', PD_SEO_OPTIMIZER_PLUGIN_DIR_URL . 'dist/pd-seo-optimizer-front.js', array(), null, true );
    }

    public function registerAdminStylesAndScripts()
    {
        //styles
        wp_enqueue_style( 'pd-seo-optimizer-admin-styles', PD_SEO_OPTIMIZER_PLUGIN_DIR_URL . 'dist/pd-seo-optimizer-admin.css' );

        //scripts
        wp_enqueue_script('jquery');
        wp_enqueue_media();
        wp_enqueue_script( 'pd-seo-optimizer-admin-scripts', PD_SEO_OPTIMIZER_PLUGIN_DIR_URL . 'dist/pd-seo-optimizer-admin.js', array('jquery'), null, true );

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

        $generator = new MetaTitleAndDescriptionGenerator(new OpenAiClient());
        $generator->generateForPosts($postIds);
        wp_send_json_success('Batch processed');
    }

    public function handleGenerateMetaTermsBatch() {
        check_ajax_referer('pd_seo_meta_nonce', 'nonce');

        $termIds = json_decode(stripslashes($_POST['ids']), true);
        if (!is_array($termIds)) {
            wp_send_json_error('Invalid term IDs');
        }

        $generator = new MetaTitleAndDescriptionGenerator(new OpenAiClient());
        $generator->generateForTerms($termIds);
        wp_send_json_success('Batch processed (terms)');
    }

    public function handleGenerateImageAltsBatch() {

        check_ajax_referer('pd_seo_meta_nonce', 'nonce');

        $postIds = json_decode(stripslashes($_POST['ids']), true);

        if (!is_array($postIds)) {
            wp_send_json_error('Invalid post IDs');
        }

        $altGenerator = new AltGenerator(new OpenAiClient());

        $results = $altGenerator->generateForPosts($postIds);

        wp_send_json_success([
            'processed_posts' => count($postIds),
            'processed_images' => $results['images_count'] ?? 0,
            'alt_text' => $results['alt_text'] ?? null
        ]);
    }

    public function handleGenerateMediaAltsBatch() {

        check_ajax_referer('pd_seo_meta_nonce', 'nonce');

        $attachmentIds = json_decode(stripslashes($_POST['ids']), true);

        if (!is_array($attachmentIds)) {
            wp_send_json_error('Invalid attachment IDs');
        }

        $altGenerator = new AltGenerator(new OpenAiClient());

        $results = $altGenerator->generateForAttachments($attachmentIds);

        wp_send_json_success([
            'processed_attachments' => count($attachmentIds),
            'processed_images' => $results['images_count'] ?? 0
        ]);
    }

    public function handleGenerateSingleAttachmentAlt() {

        check_ajax_referer('pd_seo_meta_nonce', 'nonce');

        $attachmentId = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;

        if (!$attachmentId) {
            wp_send_json_error('Invalid attachment ID');
        }

        $altGenerator = new AltGenerator(new OpenAiClient());
        $results = $altGenerator->generateForAttachments([$attachmentId]);

        wp_send_json_success([
            'processed_images' => $results['images_count'] ?? 0,
            'alt_text' => $results['alt_text'] ?? null
        ]);
    }


    public function renderMetaGeneratorPopup() {
        include PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH . 'templates/admin/meta-generator-popup.php';
    }
}