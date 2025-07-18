<?php

/**
 * Plugin Name: PD Seo Optimizer
 * Description: Seo Optimizer for WordPress.
 * Version: 1.0.3
 * Author: kkarasiewicz
 */

namespace PdSeoOptimizer;
use PdSeoOptimizer\PluginManager;

if (!defined('WPINC')) {
    die;
}

if (!defined('PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH')) {
    define('PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
}

if (!defined('PD_SEO_OPTIMIZER_PLUGIN_DIR_URL')) {
    define('PD_SEO_OPTIMIZER_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));
}

if (!defined('PD_SEO_OPTIMIZER_REPO_URL')) {
    define('PD_SEO_OPTIMIZER_REPO_URL', 'https://github.com/Premium-Digital/pd-seo-optimizer');
}

require PD_SEO_OPTIMIZER_PLUGIN_DIR_PATH . '/vendor/autoload.php';

class PdSeoOptimizer
{
    public function __construct()
    {
      load_plugin_textdomain('pd-seo-optimizer', false, dirname(plugin_basename(__FILE__)) . '/languages');
      new PluginManager();
    }
}

new PdSeoOptimizer();

register_activation_hook(__FILE__, ['PdSeoOptimizer\PluginManager', 'activate']);
