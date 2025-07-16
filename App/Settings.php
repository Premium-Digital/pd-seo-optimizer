<?php

namespace PdSeoOptimizer;

class Settings
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_init', [$this, 'registerSettings']);
    }

    public function addAdminMenu() {
        add_menu_page(
            'PD Seo Optimizer Settings',
            'PD Seo Optimizer',
            'manage_options',
            'pd_seo_optimizer_settings',
            [$this, 'renderSettingsPage'],
            'dashicons-admin-generic',
            20
        );

        // add_submenu_page(
        //     'pd_seo_optimizer_settings',
        //     __('Information', 'pd-seo-optimizer'),
        //     __('Information', 'pd-seo-optimizer'),
        //     'manage_options',
        //     'pd_seo_optimizer_info',
        //     [$this, 'renderInfoPage']
        // );
    }

    public function renderSettingsPage() {
        ob_start();
        include(PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH . 'templates/admin/options-page-template.php');
        echo ob_get_clean();
    }

    public function renderInfoPage() {
        ob_start();
        include(PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH . 'templates/admin/info-subpage-template.php');
        echo ob_get_clean();
    }

    public function registerSettings() {
        register_setting('pd_seo_optimizer_options_group', 'pd_seo_optimizer_openai_api_key');
    }

    public static function saveApiKey($apiKey) {
        update_site_option('pd_seo_optimizer_post_api_key', $apiKey);
    }
}